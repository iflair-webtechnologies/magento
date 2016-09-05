<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php'; 

require_once(Mage::getBaseDir().'/lib/tcpdf/config/tcpdf_config.php');
require_once(Mage::getBaseDir().'/lib/tcpdf/tcpdf.php');
class IFlair_Orderpdf_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    public function printOrderAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );
         
        $html_content = $this->getPdfHtmlFormat($order);
        ob_start();
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //set font
        $pdf->SetFont('helvetica', '', 10, '', 'false');
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // add a page
        $pdf->AddPage();
        //barcode
        //write1DBarcode($code, $type, $x='', $y='', $w='', $h='', $xres='', $style='', $align='')
        $pdf->write1DBarcode($order->getIncrementId(), 'C39E', 115, 5, 100, 40, 0.4, $style, 'N');
        // output the HTML content
        $pdf->writeHTML($html_content, true, false, true, false, '');
        // reset pointer to the last page
        $pdf->lastPage();
        //Close and output PDF document
        ob_end_clean();
        $pdf->Output('order_' . $order->getRealOrderId() . '.pdf', 'D');
		
		 
		// Second pdf
		$html_label = $this->getPdfHtmlLabel($order);
		ob_start();
        // create new PDF document
        $pdf1 = new TCPDF('L', 'mm', array(38.100,101.600), true, 'UTF-8', false);
        //set font
        $pdf1->SetFont('helvetica', '', 10, '', 'false');
        // set auto page breaks
        $pdf1->SetAutoPageBreak(TRUE, 0);
		$pdf1->SetMargins(0, 0, 0, False);
        // add a page
        $pdf1->AddPage();
        //barcode
        //write1DBarcode($code, $type, $x='', $y='', $w='', $h='', $xres='', $style='', $align='')
        //$pdf1->write1DBarcode($order->getIncrementId(), 'C39E', 115, 5, 100, 40, 0.4, $style, 'N');
        // output the HTML content
        $pdf1->writeHTML($html_label, true, false, true, false, '');
        // reset pointer to the last page
        $pdf1->lastPage();
        //Close and output PDF document
        ob_end_clean();
        $pdf1->Output('label/label_' . $order->getRealOrderId() . '.pdf', 'F');
		
        
        $this->prepareAttachmentFiles($order);
        exit;
    }
    public function getPdfHtmlFormat($order)
    {
       $orderItems = $order->getItemsCollection();
       
       $shippingMethod = array("flatrate_flatrate"=>"UPS Ground",
                              "matrixrate_matrixrate_67"=>"UPS Worldwide Expedited",
                              "matrixrate_matrixrate_69"=>"UPS Worldwide Express",
                              "matrixrate_matrixrate_71"=>"UPS Saturday",
                              "matrixrate_matrixrate_72"=>"UPS 2nd Day Air",
                              "matrixrate_matrixrate_73"=>"UPS Next Day Air Saver",
                              "matrixrate_matrixrate_74"=>"UPS Next Day Air Early AM",
                              "matrixrate_matrixrate_78"=>"UPS Worldwide Saver Express",
                              "matrixrate_matrixrate_80"=>"UPS Worldwide Express Plus",
                              "matrixrate_matrixrate_81"=>"UPS 3 Day Select",
                              "matrixrate_matrixrate_82"=>"UPS 2nd Day Air AM",
                              "matrixrate_matrixrate_83"=>"UPS Next Day Air",
                              "matrixrate_matrixrate_64"=>"FedEx International Economy",
                              "matrixrate_matrixrate_65"=>"FedEx Saturday",
                              "matrixrate_matrixrate_66"=>"FedEx 2Day",
                              "matrixrate_matrixrate_68"=>"FedEx Standard Overnight",
                              "matrixrate_matrixrate_70"=>"FedEx First Overnight",
                              "matrixrate_matrixrate_75"=>"FedEx International Priority",
                              "matrixrate_matrixrate_76"=>"FedEx Express Saver",
                              "matrixrate_matrixrate_77"=>"FedEx 2Day A.M.",
			                     "matrixrate_matrixrate_79"=>"FedEx Priority Overnight"
                              );
       foreach($shippingMethod as $key=>$value){
          if($key==$order->getShippingMethod()){
             $shippingLabel = $value;
          }
       }
        
        $html_content='<style>
            .box-table tr td {
                border:1px solid #000;
                text-align: center;
            }
            th {
                background-color:#e0e0e0;
                color: #000000; 
                font-weight: bold;
                padding: 10px;
                text-align: center;
            }
            .large-f{
                font-size: 20px;
            }
            .small-f{
                font-size: 15px;
            }
            h1 {
                padding:0;
                margin:0;
                line-height:0;
            }
            .border-top {
                border-bottom: 4px solid #000000;
                padding-bottom: 15px;
            }
        </style>';

        $html_content.='<table border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td align="left" width="100%">
                    <table border="0" cellpadding="3" cellspacing="0">
                        <tr>
                            <td width="35%">
                                <h1 class="logo" style="line-height:normal;" align="left">'.Mage::getModel('customer/group')->load($order->getCustomerGroupId())->getCustomerGroupCode().'</h1>Billing Dept:<br/>
                                <table cellspacing="0" cellpadding="0" border="0" width="100%" class="box-table">
                                    <tr>
                                        <td><p>Entered</p><p>Caryn</p></td>
                                        <td><p>Pulled</p></td>
                                        <td><p>Packed</p></td>
                                    </tr>
                                    <tr>
                                        <td><p>Weight</p><p>&nbsp;</p></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </table>
                            </td>
                            <td width="25%">&nbsp;</td>
                            <td width="40%">
                                <br/><br/>
                                <h1 class="small-f">'.$shippingLabel.'</h1>
                                <span class="small-f">Order ID: '.$order->getIncrementId().'</span><br/><br/>
                                <span class="small-f">Order Date: '.date('d-M-y', strtotime($order->getCreatedAt())).'</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>    
            <tr><td height="20">&nbsp;</td></tr>
            ';
            $html_content.=$this->getProductListing($order,$orderItems,1);      
            $html_content.='
            <tr><td height="10">&nbsp;</td></tr>
            <tr><td><br pagebreak="true"/></td></tr>
            <tr><td height="3" style="border-bottom:3px solid #000000;"></td></tr>
             <tr><td height="5"></td></tr>
            <tr>
                <td width="100%" align="center">
                    
                    <img width="250" src="'.str_replace("index.php/","",Mage::getUrl('')).'skin/frontend/standard-base/standard-theme/images/logo-pdf.png" /><br/><br/>
                    <h1 align="Center">Sample Center</h1>
                   
                </td>                
            </tr>
            <tr><td height="5"></td></tr>
<tr><td height="3" style="border-bottom:3px solid #000000;"></td></tr>
            <tr>
                <td width="100%">
                    <table border="0" cellpadding="3" cellspacing="0" width="100%">
                        <tr>
                            <td width="50%"><i>5100 Belmar Boulevard, Farmingdale NJ 07727 <br/> Phone: 1-800-221-809 Fax: 1-800-433-3698</i></td>
                            <td width="20%">&nbsp;</td>
                            <td width="30%" align="right"><i> Order ID:'.$order->getIncrementId().' <br>Order Date: '.date('d-M-y', strtotime($order->getCreatedAt())).'</i></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td height="20">&nbsp;</td></tr>';
            $html_content.=$this->getProductListing($order,$orderItems,2);
            $html_content.='
            <tr><td height="20"><a href="http://192.168.1.57/tarroncorporation/label/label_'.$order->getRealOrderId().'.pdf">Click for Labels</a></td></tr></table>';

        return $html_content;
    }
	public function getPdfHtmlLabel($order)
    {
       $orderItems = $order->getItemsCollection();
		
        $html_label.='<table border="0" cellpadding="0" cellspacing="0">';
    
            //$categoryIdsArr = array("15","50","51","52","57",  "26","86","87","88","93",   "23","59","66","274",   "25","77","78","79","84");
			$categoryIdsArr = array("57",  "246",  "93",  "66",  "84",  "131",  "262", "263", "264", "265", "266", "267", "269", "270", "271",  "233", "234", "235", "236", "237", "238", "239", "240", "241",  "152",  "182", "178", "179", "183", "181", "220", "228", "229", "230",  "158", "154", "275", "276", "277",  "213", "172", "212", "221", "222", "223", "214", "278");
            $pageBreak = false;
            foreach ($orderItems as $item){
               if($item->getProductType() != "configurable"){
                  $product = Mage::getModel('catalog/product')->load($item->getProductId());
                  $categoryIds = $product->getCategoryIds();
                  //echo "<pre>"; print_r($product->getCategoryIds());
                  foreach($categoryIds as $categoryId){
                     $attributePrefix = $this->getAttributeSet($product);
                     if(in_array($categoryId, $categoryIdsArr) && $product->getAttributeText($attributePrefix.'_size')){
                           if($pageBreak){
                              $html_label.='<tr><td><br pagebreak="true"/></td></tr>';
                           }
                           $pageBreak = true;   
                            $html_label.='
							<tr>
							
                            <td>
                                <table border="0" style="margin-left:150px" cellpadding="3" cellspacing="0" width="210" >
                                    <tr>
                           				<td width="130"><img src="'.str_replace("index.php/","",Mage::getUrl('')).'skin/frontend/standard-base/standard-theme/images/logo-pdf.png" width="150" /><br>
                           				<h4>'.$this->getCategoryImage($categoryId).'</h4></td>
                           				<td class="" >
                                            <table cellspacin="0" cellpadding="0" border="0" align="center" style="font-size:10px;padding-bottom:20px; border-bottom:1px thin black;">
                                                <tr>';
                                                   if((int)$item->getQtyOrdered()==1){
                                                   	$html_label.='<td valign="top">'.(int)$item->getQtyOrdered().' Item(s)</td>';
                                                   }
                                                   else{
                                                   	$html_label.='<td valign="top">'.(int)$item->getQtyOrdered().' Item(s)</td>';
                                                   }
                                                   $html_label.='<td valign="top">'.$product->getAttributeText($attributePrefix.'_size').'</td>                                    
                                                </tr>                                
                                            </table>               				
                           				</td>
                           				
                           			</tr>
                                   
                           			<tr>
                           			<td width="85%" colspan="2">
                                            <table width="100%" cellspacin="0" cellpadding="0" border="0" align="center">
                                                <tr>
                                                   <td width="80%"><p style="line-height:normal;" align="left">'.$product->getName().'</p></td>
                                                   <td width="20%" align="right"><em>'.$product->getPullLocation().'</em></td>
                                                </tr>         			
                                            </table>
                           			</td>
                           			</tr>
                                  
                                </table>
                            </td>
                        </tr>';
                     }                           
                  }   
                  
               }
            }
            //exit;
            
            
        $html_label.='</table>';

        return $html_label;
    }
    public function getProductListing($order,$orderItems,$ref)
    {
       //echo "<pre>"; print_r($order->getShippingAddress()); exit;
       //echo "<pre>"; print_r($orderItems->getData());
         $html_content.='
         <tr>
                <td width="100%">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td width="50%">
                                <h4>Ship To:</h4>';
                                $html_content.=$order->getShippingAddress()->getCompany().'<br/>'.$order->getShippingAddress()->getStreet(1).',<br/>'.$order->getShippingAddress()->getStreet(2).'<br/>'.$order->getShippingAddress()->getRegion(). $order->getShippingAddress()->getPostcode().',<br/> '.$order->getShippingAddress()->getCountryId().',<br/> '.$order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname().'
                            </td>
                            <td width="50%">
                                <h4>Req by:</h4>'.
                                $customerAddressId =  Mage::getModel('customer/customer')->load($order->getCustomerId())->getDefaultBilling();
            
                                if ($customerAddressId) {
                                        $address = Mage::getModel('customer/address')->load($customerAddressId);
                                }
                                $html_content.=$address->getCompany().' <br/>'.$address->getStreet(1).',<br/>'.$address->getStreet(2).' <br/>'.$address->getRegion(). $address->getPostcode().', '.$address->getCountryId().'
                            </td>
                        </tr>    
                    </table>
                </td>    
            </tr>
            <tr><td height="20">&nbsp;</td></tr>
            <tr>
                <td width="100%">
                    <table border="0" cellpadding="3" cellspacing="0" class="box-table">
                        <tr>
                            <td><b>Sales Rep:</b><br/>'.$customer = Mage::getModel('customer/customer')->load($order->getCustomerId())->getIpsalesrep().'</td>
                            <td><b>Customer ID: </b><br/>'.$order->getCustomerId().'</td>
                            <td><b>Shipped Date: </b><br/>'.date("d-M-y").' </td>
                        </tr>
                    </table>
                </td>
         </tr>        
         <tr><td height="20">&nbsp;</td></tr>
         <tr>
                <td width="100%">
                    <table border="0" cellpadding="3" cellspacing="0" width="100%">';
                        if($ref==2){
                              $html_content.='
                              <tr>
                                  <th width="10%" align="left">&nbsp;</th>
                                  <th width="10%" align="left">Quantity</th>
                                  <th width="35%" align="left">Product Name</th>
                                  <th width="15%" align="left">Color</th>
                                  <th width="15%" align="left">Basis Weight</th>
                                  <th width="15%" align="left">Sheet Size</th>
                              </tr>';  
                           }
                           else{
                              $html_content.='
                              <tr>
                                  <th width="10%" align="left">PL</th>
                                  <th width="10%" align="left">Quantity</th>
                                  <th width="35%" align="left">Product Name</th>
                                  <th width="15%" align="left">Color</th>
                                  <th width="15%" align="left">Basis Weight</th>
                                  <th width="15%" align="left">Sheet Size</th>
                              </tr>';  
                           }     
                        foreach ($orderItems as $item){
                           
                           if($item->getProductType() != "configurable"){
                           $product = Mage::getModel('catalog/product')->load($item->getProductId());
                           
                           $optionsArr = $item->getProductOptions();
                           if(count($optionsArr['options']) > 0)
                            {
                                foreach ($optionsArr['options'] as $option)
                                {
                                    //$optionTitle = $option['label'];
                                    //$optionId = $option['option_id'];
                                    //$optionType = $option['type'];
                                    $optionValue = '<br> Custom Order: '.$option['value'];
                                 }
                            }
                           
                           $attributePrefix = $this->getAttributeSet($product);
                           if($attributePrefix == "fs"){
                              if(!$product->getAttributeText($attributePrefix.'_size')){
                                 $promo = true;
                              }
                           }
                           else{   
                              if(!$product->getAttributeText($attributePrefix.'_basis_weight') && !$product->getAttributeText($attributePrefix.'_size')){
                                 $promo = true;
                              } 
                           }  
                           if($ref==2){
                              if($attributePrefix == "fs"){
                                 $html_content.='
                                 <tr>
                                  <td width="10%" align="left"> &nbsp; </td>
                                  <td width="10%" align="left">'.(int)$item->getQtyOrdered().'</td>
                                  <td width="35%" align="left">'.$item->getName(). $optionValue.'</td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_color').'</td>
                                  <td width="15%" align="left"> &nbsp; </td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_size').'</td>
                                 </tr>';
                                 
                              }
                              else{
                                 $html_content.='
                                 <tr>
                                  <td width="10%" align="left"> &nbsp; </td>
                                  <td width="10%" align="left">'.(int)$item->getQtyOrdered().'</td>
                                  <td width="35%" align="left">'.$item->getName(). $optionValue.'</td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_color').'</td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_basis_weight').'</td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_size').'</td>
                                 </tr>';   
                              }  
                           }
                           else{
                              if($attributePrefix == "fs"){
                                 $html_content.='
                                 <tr>
                                  <td width="10%" align="left">'.$product->getPullLocation().'</td>
                                  <td width="10%" align="left">'.(int)$item->getQtyOrdered().'</td>
                                  <td width="35%" align="left">'.$item->getName(). $optionValue.'</td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_color').'</td>
                                  <td width="15%" align="left"> &nbsp; </td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_size').'</td>
                                 </tr>'; 
                              }
                              else {
                                 $html_content.='
                                 <tr>
                                  <td width="10%" align="left">'.$product->getPullLocation().'</td>
                                  <td width="10%" align="left">'.(int)$item->getQtyOrdered().'</td>
                                  <td width="35%" align="left">'.$item->getName(). $optionValue.'</td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_color').'</td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_basis_weight').'</td>
                                  <td width="15%" align="left">'.$product->getAttributeText($attributePrefix.'_size').'</td>
                                 </tr>'; 
                              }   
                           }         
                           
                           }
                           
                           unset($optionValue);
                           //unset($basisWeight);
                           //unset($sheetSize);
                           //unset($proColor);
                        }
                        $html_content.='
                    </table>
                </td>
            </tr>
            <tr><td height="20">&nbsp;</td></tr>
            <tr>
                <td width="100%" align="left">
                    <p>Requested By: '.$address->getFirstname().' '.$address->getLastname().'</p>
                    <p>Remarks:</p>';
                    if($promo){
                       $html_content.='<h1>Promotional Material Needed*</h1>';
                    }   
                    $html_content.='
                </td>                
            </tr>
            <tr><td height="20">&nbsp;</td></tr>
            <tr>
                <td width="100%" align="left">
                    <h1>'.preg_replace("/Customer Order Comment:/", "", $order->getCustomerNote()).'</h1>
                </td>                
            </tr>
            ';
            return $html_content;   
    }
    public function getCategoryImage($categoryId)
    {
       // if any change in below id, also change with $categoryIdsArr
       //$AccentOpaque = array("15","50","51","52","57");
       //$Williamsburg = array("26","86","87","88","93"); 
       //$Carolina = array("23","59","66","274"); 
       //$Springhill = array("25","77","78","79","84");
	    $AccentOpaque = array("57");
		$Hammermill = array("246");
		$Williamsburg = array("93"); 
		$Carolina = array("66"); 
		$Springhill = array("84");
		//$PPAllBrands = array("209");
		$Envelope = array("131");
		$Forms = array("262", "263", "264", "265", "266", "267", "269", "270", "271");
		$Bristols = array("233", "234", "235", "236", "237", "238", "239", "240", "241");
		$Specialty = array("152");
		$HotCupsLids =  array("182", "178", "179", "183", "181", "220", "228", "229", "230");
		$ColdCupsLids = array("158", "154", "275", "276", "277");
		$FoodPackaging = array("213", "172", "212", "221", "222", "223", "214", "278");
       
       //For category logo
         if(in_array($categoryId, $AccentOpaque)){
            //$catImgLogo = "logo-accentopaque.jpg";
            $catImgLogo = "Accent Opaque";
         }
		 else if(in_array($categoryId, $Hammermill)){
            $catImgLogo = "Hammermill";
         }
         else if(in_array($categoryId, $Williamsburg)){
            $catImgLogo = "Williamsburg";
         }
         else if(in_array($categoryId, $Carolina)){
            $catImgLogo = "Carolina";
         }
         else if(in_array($categoryId, $Springhill)){
            $catImgLogo = "Springhill";
         }
		 else if(in_array($categoryId, $Envelope)){
            $catImgLogo = "Envelope";
         }
		 else if(in_array($categoryId, $Forms)){
            $catImgLogo = "Forms";
         }
		 else if(in_array($categoryId, $Bristols)){
            $catImgLogo = "Bristols";
         }
		 else if(in_array($categoryId, $Specialty)){
            $catImgLogo = "Specialty";
         }
		 else if(in_array($categoryId, $HotCupsLids)){
            $catImgLogo = "Hot Cups and Lids";
         }
		 else if(in_array($categoryId, $ColdCupsLids)){
            $catImgLogo = "Cold Cups and Lids";
         }
		 else if(in_array($categoryId, $FoodPackaging)){
            $catImgLogo = "Food Packaging";
         }
         return $catImgLogo;
    } 
    public function getAttributeSet($product)
    {
         $attributeSet = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId());
         $attributeSetName = $attributeSet->getAttributeSetName();
         
         if($attributeSetName=="Printing Papers"){
            //$basisWeight = $product->getAttributeText('ip_basis_weight');                             
            //$sheetSize = $product->getAttributeText('ip_size');
            //$proColor = $product->getAttributeText('ip_color');
            $prefix = "ip";
         }
         else if($attributeSetName=="Converting and Specialty Papers"){
            $prefix = "cp";
         }
         else if($attributeSetName=="Foodservice"){
            $prefix = "fs";
         }
         else if($attributeSetName=="Coated Paperboard"){
            $prefix = "co";
         }
         return $prefix;
    }
    public function prepareAttachmentFiles($order)
    {
       //if( date('d-M-y', strtotime($order->getCreatedAt())) == date('d-M-y') ){
       // Check for number of records before appending data
       $csv_filename = Mage::getBaseDir()."/OrderFile/IMPORT.txt";
       $tempcsv_filename = Mage::getBaseDir()."/OrderFile/IMPORT-TEMP.txt";
       $fp = file($csv_filename);
       
       //Read CSV file and remove order row if existing
       $id=$order->getRealOrderId();
       $curDate = date('d-M-y');
       if (($handle = fopen($csv_filename, "r")) !== FALSE) {
             
             while (($data = fgetcsv($handle)) !== FALSE) {
                        // from CSV record, check for oneday record
                        $orderDetails = Mage::getModel('sales/order')->loadByIncrementId($data[9]);
                        $csvOrderDate = date('d-M-y', strtotime($orderDetails->getCreatedAt()));
                        
                        
                 if(!count($fp) <=1 ){                 
                 if ( ($id != $data[9]) ){ //data[9]: order Id
                 //$data[10] = date('d-M-y', strtotime($orderDetails->getCreatedAt()));
                 //$data[11] = date('d-M-y');
                 
                  
                  $fptemp = fopen($tempcsv_filename, "a+");
                  if(count($fptemp) <=1 ){
                  //$list = "CustomerID1,ShipName,ShipContact,ShipAddress,ShipAddress2,ShipCity,ShipState,ShipZipCode,ShipCountry,OrderID,CarrierCode,Phone,Pay_Flag,Acct.,Billing Remarks,CompanyName,Address,City,State,ZipCode,Country,ShipPhone,ShipNotification,Email1,Email2,SystemA,SystemB,Department_Name,Sample \n";
                  //fputs($fptemp, $list);
                  }
                  $num = count($data);
                     if(!in_array($id,$data)){
                     for ($c=0; $c < $num; $c++) {
                        if($data[$c]!=""){
                              $csv .= '"'.$data[$c].'",';
                        }
                        else{
                              $csv .= ',';   
                        }   
                     }
                     $csv = rtrim($csv,",")."\n";
                    
                     // Append order data in CSV file
                     $fd = fopen ($tempcsv_filename, "a");
                     fputs($fd, $csv);
                     fclose($fd); 
                     unset($csv);
                     }
                 }
                 }
                 
             } 
             fclose($handle);
             
             unlink($csv_filename);
             rename($tempcsv_filename,$csv_filename);
       }  

       // Column headers
       if(count($fp) <=1 ){
       $csv = '"CustomerID","ShipName","ShipContact","ShipAddress","ShipAddress2","ShipCity","ShipState","ShipZipCode","ShipCountry","OrderID","CarrierCode","Phone","Pay_Flag","Acct.","Billing Remarks","CompanyName","Address","City","State","ZipCode","Country","ShipPhone","ShipNotification","Email1","Email2","SystemA","SystemB","Department_Name","Sample"'. "\n";
       }
       
       // Collect order data
       $customerID = $order->getCustomerId();
       $shipName = $order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname();
       $shipContact = $order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname();
       $shipAddress = $order->getShippingAddress()->getStreet(1);
       $shipAddress2 = $order->getShippingAddress()->getStreet(2);
       $shipCity = $order->getShippingAddress()->getCity();
       //$shipState = $order->getShippingAddress()->getRegion();
       $regionModel = Mage::getModel('directory/region')->load($order->getShippingAddress()->getRegionId());
       $shipState = $regionModel->getCode(); // Get region 2 digit code
       
       $shipZipCode = $order->getShippingAddress()->getPostcode();
       $shipCountry = $order->getShippingAddress()->getCountryId();
       $orderID = $order->getRealOrderId();
       // Get shipping code
       $shippingMethod = array("flatrate_flatrate"=>array("shipping_carrier"=>"Ground",
                                                         "system_codeA"=>"085176",
                                                         "system_codeB"=>""),
                              "matrixrate_matrixrate_67"=>array("shipping_carrier"=>"Worldwide Expedited",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_69"=>array("shipping_carrier"=>"Worldwide Express",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_71"=>array("shipping_carrier"=>"UPS Saturday",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_72"=>array("shipping_carrier"=>"2nd Day Air",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_73"=>array("shipping_carrier"=>"Next Day Air Saver",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_74"=>array("shipping_carrier"=>"UPS Next Day Air Early AM",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_78"=>array("shipping_carrier"=>"Worldwide Saver",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_80"=>array("shipping_carrier"=>"Worldwide Express Plus",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_81"=>array("shipping_carrier"=>"3 Day Select",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_82"=>array("shipping_carrier"=>"2nd Day Air AM",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_83"=>array("shipping_carrier"=>"Next Day Air",
                                                               "system_codeA"=>"085176",
                                                               "system_codeB"=>""),
                              "matrixrate_matrixrate_64"=>array("shipping_carrier"=>"03",
                                                               "system_codeA"=>"",
                                                               "system_codeB"=>"0508695"),
                              "matrixrate_matrixrate_65"=>array("shipping_carrier"=>" ",
                                                               "system_codeA"=>"",
                                                               "system_codeB"=>"0508695"),
                              "matrixrate_matrixrate_66"=>array("shipping_carrier"=>"FedEx 2Day",
                                                               "system_codeA"=>"",
                                                               "system_codeB"=>"0508695"),
                              "matrixrate_matrixrate_68"=>array("shipping_carrier"=>"05",
                                                               "system_codeA"=>"",
                                                               "system_codeB"=>"0508695"),
                              "matrixrate_matrixrate_70"=>array("shipping_carrier"=>"06",
                                                               "system_codeA"=>"",
                                                               "system_codeB"=>"0508695"),
                              "matrixrate_matrixrate_75"=>array("shipping_carrier"=>"01",
                                                               "system_codeA"=>"",
                                                               "system_codeB"=>"0508695"),
                              "matrixrate_matrixrate_76"=>array("shipping_carrier"=>"20",
                                                               "system_codeA"=>"",
                                                               "system_codeB"=>"0508695"),
                              "matrixrate_matrixrate_77"=>array("shipping_carrier"=>"49",
                                                               "system_codeA"=>"",
                                                               "system_codeB"=>"0508695"),
			                     "matrixrate_matrixrate_79"=>array("shipping_carrier"=>"01",
			                                                      "system_codeA"=>"",
			                                                      "system_codeB"=>"0508695")
                              );
       foreach($shippingMethod as $key=>$values){
          if($key==$order->getShippingMethod()){
             foreach($values as $key=>$value){
                if($key=="shipping_carrier") {$carrierCode = $value;}
                if($key=="system_codeA") { $systemA = $value; } // Use for UPS shipping
                if($key=="system_codeB") { $systemB = $value; } // Use for Fedex shipping
             }
          }
       }
	    if($systemA) 
		{
			$acct = $order->getUps();
		} // Use for UPS shipping
        else if($systemB)
		{
			$acct = $order->getFedex();
		} // Use for Fedex shipping
        
       $payFlag = "1";
       //$acct = $order->getFedex();
       $billingRemarks = $order->getRef();
       // Get registered customer data
       $customerAddressId =  Mage::getModel('customer/customer')->load($order->getCustomerId())->getDefaultBilling();
       $address = Mage::getModel('customer/address')->load($customerAddressId);
       $companyName = $address->getCompany();
       $address1 = $address->getStreet(1);
       $city = $address->getCity();
       //$state = $address->getRegion();
       $regionModel1 = Mage::getModel('directory/region')->load($address->getRegionId());
       $state = $regionModel1->getCode(); // Get region 2 digit code
       $zipCode = $address->getPostcode();
       $country = $address->getCountryId();
       $phone = $address->getTelephone();
       $shipPhone = $order->getShippingAddress()->getTelephone();
       $shipNotification = $string = preg_replace('/\s+/', ' ', trim(preg_replace("/Customer Order Comment:/", "", $order->getCustomerNote())));
       $email1 = $order->getShippingAddress()->getEmail();
       $email2 = $order->getShippingAddress()->getSuffix(); //Ship to recipient email
       
 
       $departmentName = Mage::getModel('customer/group')->load($order->getCustomerGroupId())->getCustomerGroupCode();
       $sample = "Sample";
                                
       
       // Append data to csv
       $orderDate = date('d-M-y', strtotime($order->getCreatedAt()));
       //if($orderDate == $curDate){
       $csv.= '"'.$customerID.'",'.$this->checkBlankFields($shipName).','.$this->checkBlankFields($shipContact).','.$this->checkBlankFields($shipAddress).','.$this->checkBlankFields($shipAddress2).','.$this->checkBlankFields($shipCity).','.$this->checkBlankFields($shipState).','.$this->checkBlankFields($shipZipCode).','.$this->checkBlankFields($shipCountry).','.$this->checkBlankFields($orderID).','.$this->checkBlankFields($carrierCode).','.$this->checkBlankFields($phone).','.$this->checkBlankFields($payFlag).','.$this->checkBlankFields($acct).','.$this->checkBlankFields($billingRemarks).','.$this->checkBlankFields($companyName).','.$this->checkBlankFields($address1).','.$this->checkBlankFields($city).','.$this->checkBlankFields($state).','.$this->checkBlankFields($zipCode).','.$this->checkBlankFields($country).','.$this->checkBlankFields($shipPhone).','.$this->checkBlankFields($shipNotification).','.$this->checkBlankFields($email1).','.$this->checkBlankFields($email2).','.$this->checkBlankFields($systemA).','.$this->checkBlankFields($systemB).','.$this->checkBlankFields($departmentName).',"'.$sample.'"'."\n";
       //}
              
       // Append order data in CSV file
       

   	// Open the text file
   	$f = fopen($csv_filename, "a");
   
   	// Write text
   	fwrite($f, $csv); 
   
   	// Close the text file
   	fclose($f);
   
   	
    }
    public function checkBlankFields($curVar)
    {
       if($curVar!=""){
         //return '"'.$curVar.'",'; 
		 return '"'.$curVar.'"'; 		 
       }
       else{
          //return ',';
		  return;
       }   
    }   
}
