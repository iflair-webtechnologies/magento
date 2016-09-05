<?php
class IFlair_CustomerDenied_Model_Observer extends Mage_Core_Model_Abstract
{
    public function checkLoginStatus(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if($customer->getIsDenied())
        {	
        	$message = 'You have been denied by admin.';
        	Mage::getSingleton('customer/session')->logout()->renewSession();
        	//Mage::getSingleton('customer/session')->addError($message);
        }	        
        return $this;
    }

    public function notifyCustomerStatus(Varien_Event_Observer $observer)
    {
        $cid = $observer->getEvent()->getCustomer()->getId();        
        $customer = Mage::getModel('customer/customer')->load($cid);
        $data = Mage::app()->getRequest()->getParams();

        if($data['account']['is_denied'] != $customer->getIsDenied())
        {   
            $emailTemplate = Mage::getModel('core/email_template')->loadDefault('denied_template');
            $emailTemplateVariables = array();
            $emailTemplateVariables['cname'] = $customer->getFirstname()." ".$customer->getLastname();

            if($data['account']['is_denied'] == 1)
            {    
                $emailTemplateVariables['message'] = 'You have been denied by admin.';
                $emailTemplate->setTemplateSubject('Your IP Sample Center request');
            }    
            /*else if($data['account']['is_denied'] == 0)
            {    
                $emailTemplateVariables['message'] = 'You have been activated by admin.';
                $emailTemplate->setTemplateSubject('Customer Activated by Admin');
            } */   

            $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
            $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
            $emailTemplate->setType('html');
            $emailTemplate->send($customer->getEmail(), $customer->getFirstname() . $customer->getLastname(), $emailTemplateVariables);
        }    
        return $this;
    }
}
?>