<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Get header category menu data
     * Returns structured data for header category menu
     */
    public function getHeaderCategoryData()
    {
        $category = new Category();
        $menu = $category->getAllParentWithChild();
        
        return $menu;
    }

    /**
     * Generate header category menu HTML
     * Returns HTML string for header category menu
     */
    public function generateHeaderCategoryMenu()
    {
        $menu = $this->getHeaderCategoryData();
        
        if (!$menu) {
            return '';
        }

        $html = '<li>';
        $html .= '<a href="javascript:void(0);">Category<i class="ti-angle-down"></i></a>';
        $html .= '<ul class="dropdown border-0 shadow">';
        
        foreach ($menu as $cat_info) {
            if ($cat_info->child_cat && $cat_info->child_cat->count() > 0) {
                $html .= '<li><a href="' . route('product-cat', $cat_info->slug) . '">' . $cat_info->title . '</a>';
                $html .= '<ul class="dropdown sub-dropdown border-0 shadow">';
                
                foreach ($cat_info->child_cat as $sub_menu) {
                    $html .= '<li><a href="' . route('product-sub-cat', [$cat_info->slug, $sub_menu->slug]) . '">' . $sub_menu->title . '</a></li>';
                }
                
                $html .= '</ul>';
                $html .= '</li>';
            } else {
                $html .= '<li><a href="' . route('product-cat', $cat_info->slug) . '">' . $cat_info->title . '</a></li>';
            }
        }
        
        $html .= '</ul>';
        $html .= '</li>';
        
        return $html;
    }
}
