<?php
class ControllerAccountItemList extends Controller {  
           
	public function index() { 
            //раскомментировать 4 строки ниже, если нужно чтобы доступ к списку был только у залогиненных пользователей
		/*if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/itemlist', '', 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}*/
                //раскомментировать 3 строки ниже, чтобы доступ к списку был только у определенной группы пользователей
                /*if( $this->customer->getCustomerGroupId()!=3){
			$this->redirect($this->url->link('account/logout', '', 'SSL'));
		}*/

		$this->language->load('account/itemlist');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image'); 
                               
		if (isset($this->request->get['catid'])) {
			$catid = $this->request->get['catid'];
		} else { 
			$catid = 0;
		}

		if (isset($this->request->get['limit'])) {			
			$limit = $this->request->get['limit'];
		} else {			
			$limit = 25;
		}
	
		$category_id = 0;		
		$category_info = $this->model_catalog_category->getCategory($category_id);		
                if ($category_id == 0) {
                        $category_info = array('name' => $this->language->get('text_all_products'),
                                'meta_keyword' => '',							
                                'description' => '');
                        //india style fix	
                        $this->request->get['path'] = 0;
                        //india style fix							
                }	
                // Set the last category breadcrumb		
                $url = '';

                    $data = array(
                            'filter_category_id' => $catid,
                            //'filter_filter'      => $filter, 
                            //'sort'               => $sort,
                            'limit'              => $limit,
                            'start'              => 0
                            //'coolfilter'         => $coolfilter
                    );

                    $product_total = $this->model_catalog_product->getTotalProducts($data); 
                    $results = $this->model_catalog_product->getProducts($data);
                    $prod_counter = 1;
                    foreach ($results as $result) {                            
                        if ($result['image']) {
                                $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                        } else {
                                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                        }
                        $categories = $this->model_catalog_product->getCategories($result['product_id']);
                        if ($categories){
                        $categories_info = $this->model_catalog_category->getCategory($categories[0]['category_id']);
                        }
                            //ocshop benefits
                        $data['products'][] = array(
                                    'thumb'       => $image,
                                'product_id'  => $result['product_id'],
                                'category_id'  => $categories_info['category_id'],
                                'category_name'  => $categories_info['name'],
                                'image'  => $result['image'],
                                'pr_count'  => $prod_counter,
                                'name'        => $result['name'],
                                'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 300) . '..',
                                'price'       => $result['price'],
                                'href'        => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url)
                        );                            
                        $prod_counter++;
                    }
                    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/itemlist')) {
                            $this->template = $this->config->get('config_template') . '/template/account/itemlist';
                    } else {
                            $this->template = 'default/template/account/itemlist';
                    }
                $data['AllCategories'] = array();
		$data['WithSubCategories'] = array();
		$data['AllCategories'] = $this->model_catalog_category->getCategories(array());
                foreach($data['AllCategories'] as $Category){
                $filter_data = array(
                    'filter_category_id' => $Category['category_id'],
                    'start' => 0);
                $product_total = $this->model_catalog_product->getTotalProducts($filter_data); //Find prods quatity in this category
                $tmpArr = array('category_id' =>$Category['category_id'], 'name' => $Category['name'], 'product_total'=>$product_total);
                array_push($data['WithSubCategories'], $tmpArr);
                $SubCategories = $this->model_catalog_category->getCategories($Category['category_id']); 
                if(count($SubCategories)>0){
                       foreach($SubCategories as $SubCategory){
                           $filter_data = array(
                                'filter_category_id' => $SubCategory['category_id'],                                   
                                'start' => 0);
                $product_total = $this->model_catalog_product->getTotalProducts($filter_data);//Find prods quatity in this category                               
                           $tmpArr1 = array('category_id' =>$SubCategory['category_id'], 'name' => $SubCategory['name'], 'product_total'=>$product_total);
                           array_push($data['WithSubCategories'], $tmpArr1);
                           $SubSubCategories = $this->model_catalog_category->getCategories($SubCategory['category_id']); 
                           if(count($SubSubCategories)>0){
                               foreach($SubSubCategories as $SubSubCategory){
                                   $filter_data = array(
                                'filter_category_id' => $SubSubCategory['category_id'],                                   
                                'start' => 0);
                $product_total = $this->model_catalog_product->getTotalProducts($filter_data);//Find prods quatity in this category
                            $tmpArr2 = array('category_id' =>$SubSubCategory['category_id'], 'name' => $SubSubCategory['name'], 'product_total'=>$product_total);
                           array_push($data['WithSubCategories'], $tmpArr2);                               
                               }
                           }
                       }
                       
                } 
      $data['limits'] = array();
			$limits = array_unique(array(5, 25, 50, 75, 100, 1000));
			sort($limits);
			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('account/itemlist', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
				);
			}
      if (isset($this->request->get['limit'])) {
          $url .= '&limit=' . $this->request->get['limit'];
      }
      if (isset($this->request->get['catid'])) {
		  $url .= '&catid=' . $this->request->get['catid'];
			}
              
    $data['user_group_idi'] = $this->customer->getGroupId();                
    $this->document->setTitle($this->language->get('heading_title'));
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
    $data['heading_title'] = $this->language->get('heading_title');
    $data['test'] = "Test variable !";
    $data['text_refine'] = $this->language->get('text_refine');
    $data['text_empty'] = $this->language->get('text_empty');			
    $data['text_quantity'] = $this->language->get('text_quantity');
    $data['text_manufacturer'] = $this->language->get('text_manufacturer');
    $data['text_model'] = $this->language->get('text_model');
    $data['text_price'] = $this->language->get('text_price');
    $data['text_points'] = $this->language->get('text_points');
    $data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
    $data['text_display'] = $this->language->get('text_display');
    $data['text_list'] = $this->language->get('text_list');
    $data['text_grid'] = $this->language->get('text_grid');
    $data['text_sort'] = $this->language->get('text_sort');
    $data['text_limit'] = $this->language->get('text_limit');
    $data['text_benefits'] = $this->language->get('text_benefits');
    $data['button_cart'] = $this->language->get('button_cart');
    $data['button_wishlist'] = $this->language->get('button_wishlist');
    $data['button_compare'] = $this->language->get('button_compare');
    $data['button_continue'] = $this->language->get('button_continue');
    $data['breadcrumbs'] = array();
    $data['catid'] = $catid;
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/itemlist', '', true)
		);
        $this->response->setOutput($this->load->view('account/itemlist', $data));
		
        }        
    }
	
	public function jsonFunc(){
            //echo "TEST!";
            $this->language->load('account/itemlist');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image'); 
                               
		if (isset($this->request->get['catid'])) {
			$catid = $this->request->get['catid'];
		} else { 
			$catid = 0;
                }
                
                if (isset($this->request->get['limit'])) {			
			$limit = $this->request->get['limit'];
		} else {			
			$limit = 5000;
		}
	
		$category_id = 0;
		
		$category_info = $this->model_catalog_category->getCategory($category_id);
					if ($category_id == 0) {
						$category_info = array('name' => $this->language->get('text_all_products'),
							
							'meta_keyword' => '',							
							'description' => '');
						//india style fix	
						$this->request->get['path'] = 0;
						//india style fix							
					}		
                $url = '';

                    $data = array(
                            'filter_category_id' => $catid,
                            //'filter_filter'      => $filter, 
                            //'sort'               => $sort,
                            'limit'              => $limit,
                            'start'              => 0
                            //'coolfilter'         => $coolfilter
                    );

                    $product_total = $this->model_catalog_product->getTotalProducts($data); 

                    $results = $this->model_catalog_product->getProducts($data);

                    foreach ($results as $result) {                           
                            if ($result['image']) {
                                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                            } else {
                                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                            }

                        $categories = $this->model_catalog_product->getCategories($result['product_id']);
                        if ($categories){
                        $categories_info = $this->model_catalog_category->getCategory($categories[0]['category_id']);
                        }
                            $data['products'][] = array(
					'thumb'       => $image,
                                    'product_id'  => $result['product_id'],
                                    'category_id'  => $categories_info['category_id'],
                                    'category_name'  => $categories_info['name'],
                                    'image'  => $result['image'],
                                    'name'        => $result['name'],
                                    'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 300) . '..',
                                    'price'       => $result['price'],
                                    'href'        => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url)
                            );
                    }

                    $productsJson = json_encode($data['products']);
                    echo $productsJson;

    }
    
}
?>
