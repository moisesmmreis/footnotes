<?php
/*
Plugin Name: Footnotes
Plugin URI: https://moisesmmreis.vercel.app
Description: Add a footnote function to the website using Tailwind CSS
Version: 0.1.0
Author: Moisés Moreira Reis
Author URI: https://moisesmmreis.vercel.app
Text Domain: footnotes
*/

// Add a filter to modify the content
add_filter('the_content', 'footnotes_add_links');

// Function to add reference links
function footnotes_add_links($content)
{
    $references = array();

    // Search for references in the content and replace them with a unique identifier
    $content = preg_replace_callback(
        '/\[ref\](.*?)\[\/ref\]/',
        function ($match) use (&$references) {
            $id = count($references) + 1;
            $references[$id] = $match[1];
            return '<sup id="sup-' . $id . '"><a class="text-blue-600 text-xs" href="#ref-' . $id . '">' . $id . '</a></sup>';
        },
        $content
    );

    // Display the references
    if (!empty($references)) {
        $output = '<hr><h3 class="capitalize font-display text-4xl font-black mb-6 after:block after:h-[6px] after:mt-2 after:w-full after:border-t after:border-b">Nossas fontes</h3><ol>';
        foreach ($references as $id => $reference) {
            $output .= '<li class="" id="ref-' . $id . '">';
            $output .= '<a class="underline-none text-blue-600 max-h-fit !pr-6" href="#sup-' . $id . '">';
            $output .= '&uarr;';
            $output .= '</a>';
            $output .= '<span>' . $reference . '</span>';
            $output .= '</li>';
        }
        $output .= '</ol>';
        $content .= $output;
    }

    return $content;
}
