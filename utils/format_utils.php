<?php

/**
 * Formats a JSON string of expertise categories into a readable list
 * 
 * @param string $expertise JSON string of expertise categories
 * @return string Formatted list of expertise areas
 */
function formatExpertise($expertise) {
    // If empty or invalid JSON, return empty string
    if (empty($expertise)) return '';
    
    try {
        // Decode JSON string
        $areas = json_decode($expertise, true);
        
        if (!is_array($areas)) return '';
        
        // Map of raw values to display names
        $displayNames = [
            'nutrition' => 'Nutrition',
            'wellness' => 'Wellness',
            'mental_health' => 'Mental Health',
            'fitness' => 'Fitness',
            'lifestyle' => 'Lifestyle',
            'holistic' => 'Holistic Health'
        ];
        
        // Format each area
        $formatted = array_map(function($area) use ($displayNames) {
            return $displayNames[$area] ?? ucfirst(str_replace('_', ' ', $area));
        }, $areas);
        
        // Join with commas and 'and' for the last item if multiple items exist
        if (count($formatted) > 1) {
            $last = array_pop($formatted);
            return implode(', ', $formatted) . ' & ' . $last;
        }
        
        return implode(', ', $formatted);
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Gets an array of expertise areas from JSON string
 * 
 * @param string $expertise JSON string of expertise categories
 * @return array Array of expertise areas
 */
function getExpertiseArray($expertise) {
    if (empty($expertise)) return [];
    
    try {
        $areas = json_decode($expertise, true);
        return is_array($areas) ? $areas : [];
    } catch (Exception $e) {
        return [];
    }
} 