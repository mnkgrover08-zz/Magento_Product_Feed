<?php 
namespace Kraftly\KraftlyFeed\Controller\Index; 
 
class Index extends \Magento\Framework\App\Action\Action {
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;
    /**      * @param \Magento\Framework\App\Action\Context $context      */
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)     {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Kraftly Product Feed'));
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
	$offset = $this->getRequest()->getParam('offset');
	$limit = $this->getRequest()->getParam('limit');
	if($limit > 20){
	   $limit = 20;
	}
	if(!isset($offset) || !isset($limit)){
	$offset = 0;
	$limit = 20;
	}
        $collection = $productCollection->create()
                    ->addAttributeToSelect('*')->setPage($offset,$limit)->load();
        $products = array();
        $count = $productCollection->create()
                    ->addAttributeToSelect('*')->count();
	$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        foreach ($collection as $key => $product){
            $products[$key] = $product->getData();
		$qty = $StockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
		$products[$key]['qty'] = $qty;
        }
	$finalProductArray = array();
	foreach($products as $key => $product){
		$tmpArray = array();
		$entity_id = $product['entity_id'];
		$tmpArray[$entity_id]['product_title'] = $product['name'];
		$tmpArray[$entity_id]['sku'] = $product['sku']; 
		$tmpArray[$entity_id]['product_image'] = $product['image'];
		$tmpArray[$entity_id]['post_id'] = $product['entity_id'];
		$tmpArray[$entity_id]['stock_status'] = $product['quantity_and_stock_status'];
		$tmpArray[$entity_id]['stock'] = $product['qty'];

		if( isset($product['type_id']) && $product['type_id']=='downloadable'){
                $tmpArray[$entity_id]['downloadable'] = 'yes';
		$tmpArray[$entity_id]['virtual'] = 'yes';
                }else{
		 $tmpArray[$entity_id]['downloadable'] = 'no';
                $tmpArray[$entity_id]['virtual'] = 'no';
                }

		if( isset($product['tax_class_id']) && $product['tax_class_id'] != 0){
		$tmpArray[$entity_id]['tax_status'] = 'Taxable';
		}else{
		$tmpArray[$entity_id]['tax_status'] = "Not Taxable";
		}
		$tmpArray[$entity_id]['stock'] = $product['qty'];
		$tmpArray[$entity_id]['weight'] = $product['weight'];
		$tmpArray[$entity_id]['regular_price'] = $product['price'];
		$tmpArray[$entity_id]['sale_price'] = $product['special_price'];		
		$tmpArray[$entity_id]['description'] = $product['short_description'];
		$tmpArray[$entity_id]['content'] = $product['description'];
		$finalProductArray[] = $tmpArray;

	}
        $finalProductsArray = array('count'=>$count,'products'=>$finalProductArray);
        echo json_encode($finalProductsArray);die();
        return $resultPage;
    }
}
 
