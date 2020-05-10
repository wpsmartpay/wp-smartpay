<?php

/**
 * Gets the Download type, either default or "bundled"
 *
 * @since 1.6
 * @param int $download_id Download ID
 * @return string $type Download type
 */
function edd_get_download_type($download_id = 0)
{
    $download = new EDD_Download($download_id);
    return $download->type;
}

/**
 * Gets all download files for a product
 *
 * Can retrieve files specific to price ID
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @param int $variable_price_id Variable pricing option ID
 * @return array $files Download files
 */
function edd_get_download_files($download_id = 0, $variable_price_id = null)
{
    $download = new EDD_Download($download_id);
    return $download->get_files($variable_price_id);
}

/**
 * Checks to see if a download has variable prices enabled.
 *
 * @since 1.0.7
 * @param int $download_id ID number of the download to check
 * @return bool true if has variable prices, false otherwise
 */
function edd_has_variable_prices($download_id = 0)
{

    if (empty($download_id)) {
        return false;
    }

    $download = new EDD_Download($download_id);
    return $download->has_variable_prices();
}
