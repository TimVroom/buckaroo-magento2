<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Test\Unit\Gateway\Http\TransactionBuilder;

use TIG\Buckaroo\Test\BaseTest;

class OrderTest extends BaseTest
{
    /**
     * @var \TIG\Buckaroo\Gateway\Http\TransactionBuilder\Order
     */
    protected $object;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory|\Mockery\MockInterface
     */
    protected $configProvider;

    public function setUp()
    {
        parent::setUp();

        $this->configProvider = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Factory::class);

        $this->object = $this->objectManagerHelper->getObject(
            \TIG\Buckaroo\Gateway\Http\TransactionBuilder\Order::class,
            ['configProviderFactory' => $this->configProvider]
        );
    }

    public function testGetBody()
    {
        $expected = [
            'Currency' => 'EUR',
            'AmountDebit' => 50,
            'AmountCredit' => 0,
            'Invoice' => 999,
            'Order' => 999,
            'Description' => 'transactionLabel',
            'ClientIP' => [
                '_' => '127.0.0.1',
                'Type' => 'IPv4',
            ],
            'StartRecurrent' => 1,
            'Services' => [
                'Service' => 'servicesString',
            ],
        ];

        $this->object->amount = 50;
        $this->object->currency = 'EUR';
        $this->object->setStartRecurrent($expected['StartRecurrent']);
        $this->object->setServices($expected['Services']['Service']);

        $account = \Mockery::mock('\TIG\Buckaroo\Model\ConfigProvider\Account');
        $account->shouldReceive('getTransactionLabel')->andReturn($expected['Description']);
        $this->configProvider->shouldReceive('get')->once()->with('account')->andReturn($account);

        $order = \Mockery::mock(\Magento\Sales\Model\Order::class);
        $order->shouldReceive('getIncrementId')->twice()->andReturn($expected['Invoice']);
        $order->shouldReceive('getRemoteIp')->andReturn($expected['ClientIP']['_']);
        $this->object->setOrder($order);

        $result = $this->object->getBody();
        foreach ($expected as $key => $value) {
            $valueToTest = $value;

            if (is_array($valueToTest)) {
                $valueToTest = (object)$value;
            }

            $this->assertEquals($valueToTest, $result[$key]);
        }
    }
}
