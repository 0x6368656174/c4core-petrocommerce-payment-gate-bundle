<?php

namespace ItQuasar\C4CorePetrocommercePaymentGateBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    const GeneralOrderEntity = 'order_entity';
    
    const IaIaUrl = 'ia_url';
    const IaMerchantId = 'merchant_id';
    const IaMerchantName = 'merchant_name';
    const IaCurrency = 'currency';
    const IaRequestSignKeyFile = 'request_sign_key_file';
    const IaResponseSignKeyFile = 'response_sign_key_file';

    const IaIaUrlDev = 'ia_url_dev';
    const IaMerchantIdDev = 'merchant_id_dev';
    const IaTerminalIdDev = 'terminal_id_dev';
    const IaRequestSignKeyFileDev = 'request_sign_key_file_dev';
    const IaResponseSignKeyFileDev = 'response_sign_key_file_dev';

    const NotifNotifUrl = 'notif_url';
    const NotifClientCertFile = 'notif_client_cert_file';
    const NotifClientKeyFile = 'notif_client_key_file';
    const NotifCaCertFile = 'notif_ca_cert_file';

    const NotifNotifUrlDev = 'notif_url_dev';
    const NotifClientCertFileDev = 'notif_client_cert_file_dev';
    const NotifClientKeyFileDev = 'notif_client_key_file_dev';
    const NotifCaCertFileDev = 'notif_ca_cert_file_dev';

    private $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->alias);

        $rootNode->
            children()
                ->scalarNode(self::GeneralOrderEntity)->isRequired()->cannotBeEmpty()->end()

                ->scalarNode(self::IaIaUrl)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::IaMerchantId)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::IaMerchantName)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::IaCurrency)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::IaRequestSignKeyFile)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::IaResponseSignKeyFile)->isRequired()->cannotBeEmpty()->end()

                ->scalarNode(self::IaIaUrlDev)->end()
                ->scalarNode(self::IaMerchantIdDev)->end()
                ->scalarNode(self::IaTerminalIdDev)->end()
                ->scalarNode(self::IaRequestSignKeyFileDev)->end()
                ->scalarNode(self::IaResponseSignKeyFileDev)->end()

                ->scalarNode(self::NotifNotifUrl)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::NotifClientCertFile)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::NotifClientKeyFile)->isRequired()->cannotBeEmpty()->end()
                ->scalarNode(self::NotifCaCertFile)->isRequired()->cannotBeEmpty()->end()

                ->scalarNode(self::NotifNotifUrlDev)->end()
                ->scalarNode(self::NotifClientCertFileDev)->end()
                ->scalarNode(self::NotifClientKeyFileDev)->end()
                ->scalarNode(self::NotifCaCertFileDev)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
