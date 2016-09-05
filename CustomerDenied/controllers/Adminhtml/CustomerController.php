<?php
require_once 'Mage/Adminhtml/controllers/CustomerController.php';

class IFlair_CustomerDenied_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
    public function massDeniedAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
        } else {
            try {
                $customer = Mage::getModel('customer/customer');
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId); 
                    $customer->setIsDenied(1);
                    $customer->save();

                    $emailTemplate = Mage::getModel('core/email_template')->loadDefault('denied_template');
                    $emailTemplateVariables = array();
                    $emailTemplateVariables['cname'] = $customer->getFirstname()." ".$customer->getLastname();
                    $emailTemplateVariables['message'] = 'You have been denied by admin.';

                    $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
                    $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
                    $emailTemplate->setType('html');
                    $emailTemplate->setTemplateSubject('Your IP Sample Center request');
                    $emailTemplate->send($customer->getEmail(), $customer->getFirstname() . $customer->getLastname(), $emailTemplateVariables);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were denied.', count($customersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    public function massActiveAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
        } else {
            try {
                $customer = Mage::getModel('customer/customer');
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId); 
                    $customer->setIsDenied(0);
                    $customer->save();

                    $emailTemplate = Mage::getModel('core/email_template')->loadDefault('denied_template');
                    $emailTemplateVariables = array();
                    $emailTemplateVariables['cname'] = $customer->getFirstname()." ".$customer->getLastname();
                    $emailTemplateVariables['message'] = 'You have been activated by admin.';

                    $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
                    $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
                    $emailTemplate->setType('html');
                    $emailTemplate->setTemplateSubject('Customer Activated by Admin');
                    //$emailTemplate->send($customer->getEmail(), $customer->getFirstname() . $customer->getLastname(), $emailTemplateVariables);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were activated.', count($customersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
?>