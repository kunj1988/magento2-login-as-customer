<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Plugin\Button;

use \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor;
use \Magento\Framework\View\Element\AbstractBlock;
use \Magento\Backend\Block\Widget\Button\ButtonList;

/**
 * Class ToolbarPlugin
 * @package Magento\LoginAsCustomerAdvanced\Plugin\Button
 */
class ToolbarPlugin
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * ToolbarPlugin constructor.
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->authorization = $authorization;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     */
    public function beforePushButtons(
        Interceptor $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ):void {
        $order = false;
        $nameInLayout = $context->getNameInLayout();

        if ('sales_order_edit' == $nameInLayout) {
            $order = $context->getOrder();
        } elseif ('sales_invoice_view' == $nameInLayout) {
            $order = $context->getInvoice()->getOrder();
        } elseif ('sales_shipment_view' == $nameInLayout) {
            $order = $context->getShipment()->getOrder();
        } elseif ('sales_creditmemo_view' == $nameInLayout) {
            $order = $context->getCreditmemo()->getOrder();
        }
        if ($order) {
            if ($this->isAllowed()) {
                if (!empty($order['customer_id'])) {
                    $buttonUrl = $context->getUrl('loginascustomer/login/login', [
                        'customer_id' => $order['customer_id']
                    ]);
                    $buttonList->add(
                        'guest_to_customer',
                        ['label' => __('Login As Customer'), 'onclick' => 'window.open(\'' . $buttonUrl . '\')', 'class' => 'reset'],
                        -1
                    );
                }
            }
        }
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    private function isAllowed(): bool
    {
        return (bool)$this->authorization->isAllowed('Magento_LoginAsCustomer::login_button');
    }
}
