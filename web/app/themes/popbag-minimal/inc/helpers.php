<?php
/**
 * Helpers (assets, logo, misc).
 */
if (!defined('ABSPATH')) {
	exit;
}

function popbag_asset_uri(string $relative_path): string {
	$relative_path = ltrim($relative_path, '/');
	return get_theme_file_uri($relative_path);
}

/**
 * Render site logo (navbar).
 *
 * Priority:
 * - Custom Logo (Customizer) if set
 * - Fallback theme SVG in /assets/images/logo-orizzontale.svg
 */
function popbag_render_site_logo(string $wrapper_class = '', string $img_class = 'h-12 w-auto md:h-14 lg:h-16'): void {
	$site_name = get_bloginfo('name');
	$wrapper_class = trim('flex items-center ' . $wrapper_class);

	$custom_logo_id = (int) get_theme_mod('custom_logo');
	if ($custom_logo_id) {
		$src = wp_get_attachment_image_url($custom_logo_id, 'full');
		$alt = get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true);
		$alt = $alt ? $alt : $site_name;

		if ($src) {
			?>
			<a href="<?php echo esc_url(home_url('/')); ?>" class="<?php echo esc_attr($wrapper_class); ?>" aria-label="<?php echo esc_attr($site_name); ?>">
				<img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr($alt); ?>" class="<?php echo esc_attr($img_class); ?>" loading="eager" decoding="async" />
			</a>
			<?php
			return;
		}
	}

	$logo_src = popbag_asset_uri('assets/images/logo-orizzontale.svg');
	?>
	<a href="<?php echo esc_url(home_url('/')); ?>" class="<?php echo esc_attr($wrapper_class); ?>" aria-label="<?php echo esc_attr($site_name); ?>">
		<img src="<?php echo esc_url($logo_src); ?>" alt="<?php echo esc_attr($site_name); ?>" class="<?php echo esc_attr($img_class); ?>" loading="eager" decoding="async" />
	</a>
	<?php
}

/**
 * Utility: merge class strings (Tailwind-friendly).
 *
 * Accepts strings and arrays (nested). Removes empties and trims.
 */
function popbag_classnames(...$parts): string {
	$out = [];
	$stack = $parts;

	while (!empty($stack)) {
		$part = array_shift($stack);
		if (is_array($part)) {
			$stack = array_merge($part, $stack);
			continue;
		}
		if (!is_string($part)) {
			continue;
		}
		$part = trim($part);
		if ($part !== '') {
			$out[] = $part;
		}
	}

	return trim(implode(' ', $out));
}

/**
 * UI: consistent button classes across templates.
 */
function popbag_button_classes(string $variant = 'primary', string $size = 'md', string $extra = ''): string {
	$base = 'inline-flex items-center justify-center rounded-full font-bold uppercase tracking-[0.18em] transition hover:-translate-y-px';

	$size_classes = match ($size) {
		'sm' => 'px-4 py-2 text-xs',
		'lg' => 'px-7 py-4 text-sm',
		default => 'px-6 py-3 text-sm',
	};

	$variant_classes = match ($variant) {
		'secondary' => 'bg-[#003745] text-white hover:shadow-md',
		'outline'   => 'border border-[#003745]/15 bg-white text-[#003745] hover:shadow-sm',
		'ghost'     => 'bg-transparent text-[#003745] hover:bg-[#003745]/5',
		default     => 'bg-[#FF2030] text-white hover:shadow-md',
	};

	return popbag_classnames($base, $size_classes, $variant_classes, $extra);
}

/**
 * Walker: primary navigation with dropdown support.
 *
 * - Adds classes/ARIA for items with children
 * - Renders a dedicated toggle button for mobile sub-menus
 */
class Popbag_Walker_Primary_Nav extends Walker_Nav_Menu {
	/**
	 * Ensure `$args->has_children` is available in start_el.
	 */
	public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {
		if (!$element) {
			return;
		}

		$id_field = $this->db_fields['id'];
		if (is_array($args) && isset($args[0]) && is_object($args[0])) {
			$args[0]->has_children = !empty($children_elements[$element->$id_field]);
		}

		parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
	}

	public function start_lvl(&$output, $depth = 0, $args = null) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class=\"sub-menu popbag-sub-menu\" role=\"menu\">\n";
	}

	public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
		if (!$item instanceof WP_Post) {
			return;
		}

		$indent = ($depth) ? str_repeat("\t", $depth) : '';
		$classes = empty($item->classes) ? [] : (array) $item->classes;

		$has_children = is_object($args) && !empty($args->has_children);
		if ($has_children) {
			$classes[] = 'popbag-has-dropdown';
		}

		$class_names = join(' ', array_filter(array_map('sanitize_html_class', $classes)));
		$class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

		$output .= $indent . '<li' . $class_names . '>';

		$atts = [];
		$atts['title']  = !empty($item->attr_title) ? $item->attr_title : '';
		$atts['target'] = !empty($item->target) ? $item->target : '';
		$atts['rel']    = !empty($item->xfn) ? $item->xfn : '';
		$atts['href']   = !empty($item->url) ? $item->url : '';

		if ($has_children) {
			$atts['aria-haspopup'] = 'true';
			$atts['aria-expanded'] = 'false';
		}

		$attributes = '';
		foreach ($atts as $attr => $value) {
			if ('' === $value) {
				continue;
			}
			$value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
			$attributes .= ' ' . $attr . '="' . $value . '"';
		}

		$title = apply_filters('the_title', $item->title, $item->ID);
		$title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);

		$item_output  = is_object($args) ? ($args->before ?? '') : '';
		$item_output .= '<a' . $attributes . '>';
		$item_output .= (is_object($args) ? ($args->link_before ?? '') : '') . esc_html($title) . (is_object($args) ? ($args->link_after ?? '') : '');

		if ($has_children) {
			// Caret (visual indicator).
			$item_output .= '<span class="ml-1 inline-flex items-center" aria-hidden="true"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg></span>';
		}

		$item_output .= '</a>';

		$item_output .= is_object($args) ? ($args->after ?? '') : '';

		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
	}
}



