<?php

function tct_manager_enqueue_scripts()
{
	wp_enqueue_script('tct-manager-angular', plugins_url('/js/angular.min.js', __FILE__));
    wp_enqueue_script('tct-manager-angular-cookies', plugins_url('/js/angular-cookies.min.js', __FILE__));
    wp_enqueue_script('tct-manager-angular-resource', plugins_url('/js/angular-resource.min.js', __FILE__));

    wp_enqueue_script('tct-manager-script', plugins_url('/js/tct-manager-script.js', __FILE__), array('jquery', 'tct-manager-angular', 'tct-manager-angular-resource', 'tct-manager-angular-cookies'));;
	wp_enqueue_style('tct-manager-style', plugins_url('/css/tct-manager-style.css', __FILE__));
}



function tct_manager_content($content)
{
    if (is_single()) 
    {
        if (strpos($content, 'class="tct-manager-post-content"') !== false)
        {
            // $buttonTemplate = '<div class="tct-manager-to-cart" ng-if="isToCartShown" ng-cloak>
            //     <div class="tct-manager-input-group">
            //     <span class="units">{{ toCartData.count }} <span ng-bind-html="units"></span></span>
            //         <input type="text" class="tct-manager-input" ng-model="toCartData.count" ng-disabled="isLoading" ng-change="updateCount()">

            //         <button class="tct-manager-button" ng-click="addToCart()" ng-disabled="isLoading" ng-if="!isLinkShown">Добавить в корзину</button>
            //         <a href="/cart" class="tct-manager-light-button" ng-if="isLinkShown">✓ В корзине</a>
            //     </div>
            // </div>';

            // $content = '<div ng-app="tctApp" ng-controller="toCartController" ng-init="init(\'' . str_replace(['(', ')', '"'], '', $GLOBALS['post']->post_title) . '\')">' . $content . $buttonTemplate . '</div>';

            $inStockTemplate = '<div class="tct-manager-in-stock" ng-if="isToCartShown" ng-cloak>
                &nbsp;
                <p><strong>Наличие на складе:</strong></p>
                <table>
                    <tbody>
                        <tr ng-if="productGroup.set_pair">
                            <td>
                            </td>
                            <td>
                                <strong>1 элемент</strong>
                            </td>
                            <td>
                                <strong>2 элемент</strong>
                            </td>
                        </tr>
                        <tr ng-repeat="product in productGroup.products">
                            <td>
                                <strong>{{ product.variation }}<strong>
                            </td>
                            <td>
                                <span ng-bind-html="product.in_stock"></span>
                            </td>
                            <td ng-if="productGroup.set_pair">
                                <span ng-bind-html="product.in_stock_pair"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>';

            $content = '<div ng-app="tctApp" ng-controller="toCartController" ng-init="init(\'' . str_replace(['(', ')', '"'], '', $GLOBALS['post']->post_title) . '\')">' . $content . $inStockTemplate . '</div>';
        } 
    }

    return $content;
}


function tct_manager_insert_post_data($data)
{
	$title = $data['post_title'];
    $url = 'https://manager.582907.ru/wp-api/product/post?' . http_build_query(['title' => $title]);
    $response = json_decode(file_get_contents($url), true);

    if (!empty($response['content']))
    {
        $pos = strpos($data['post_content'], 'tct-manager-post-content');
        if ($pos !== false)
        {
            $data['post_content'] = preg_replace('/<div class=\\\"tct-manager-post-content[\s\S]*<!-- tct-manager-post-content -->/', 
                $response['content'], 
                $data['post_content']); 
        }
        else 
        {
            $data['post_content'] .= $response['content'];
        }
    }
    
    if (!empty($response['excerpt']))
    {
        $pos = strpos($data['post_excerpt'], 'tct-manager-post-excerpt');
        if ($pos !== false)
        {
            $data['post_excerpt'] = preg_replace('/<span class=\\\"tct-manager-post-excerpt[\s\S]*<!-- tct-manager-post-excerpt -->/', 
                $response['excerpt'], 
                $data['post_excerpt']); 
        }
        else
        {
            $data['post_excerpt'] .= $response['excerpt'];
        }
    }

    return $data;
}


function tct_page_template($page_template)
{
    if (is_page('cart')) 
    {
        $page_template = dirname( __FILE__ ) . '/cart.php';
    }
    return $page_template;
}

