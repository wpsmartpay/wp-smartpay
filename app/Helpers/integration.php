<?php

use SmartPay\Foundation\Integration;
use SmartPay\Modules\Integration\Integration as IntegrationModule;

function smartpay_integrations()
{
    return apply_filters('smartpay_integrations', IntegrationModule::getIntegrations());
}

function smartpay_active_integrations()
{
    $integrations = smartpay_integrations();
    $activated_integrations = smartpay_get_activated_integrations();

    return array_intersect_key($integrations, array_flip($activated_integrations));
}

function smartpay_get_activated_integrations()
{
    $integrations = (array) smartpay_get_option('integrations', []);

    $activated_integrations = array_filter($integrations, function ($integration) {
        if ($integration['active']) {
            return $integration;
        }
    });

    return array_keys($activated_integrations);
}

function smartpay_integration_is_installed($integration)
{
    return isset($integration['manager']) && smartpay_integration_get_manager($integration['manager']);
}

function smartpay_integration_get_manager(string $manager): Integration
{
    return apply_filters('smartpay_integration_manager', IntegrationModule::getIntegrationManager($manager), $manager);
}

function smartpay_integration_get_config(Integration $integration): array
{
    return $integration->config();
}

function smartpay_integration_get_not_installed_message(string $type): void
{
    switch ($type) {
        case 'pro':
        default:
            $message = '<a href="#" class="btn btn-sm flex-grow-1 text-decoration-none btn-primary">Upgrade to pro</a>';

            break;
    }

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
    echo apply_filters('smartpay_integration_get_not_installed_message', $message, $type);
}
