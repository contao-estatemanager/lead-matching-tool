<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/lead-matching-tool
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

namespace ContaoEstateManager\LeadMatchingTool;

use Contao\Config;
use Contao\Environment;
use ContaoEstateManager\EstateManager;

class AddonManager
{
    /**
     * Addon name
     * @var string
     */
    public static $name = 'Lead Matching Tool';

    /**
     * Addon config key
     * @var string
     */
    public static $key  = 'addon_lead_matching_tool_license';

    /**
     * Is initialized
     * @var boolean
     */
    public static $initialized  = false;

    /**
     * Is valid
     * @var boolean
     */
    public static $valid  = false;

    /**
     * Licenses
     * @var array
     */
    private static $licenses = [
        '60833b214cad4d1dc8b1cc948bfab960',
        '846fed5ca96c2c0f1639d77459fa7e27',
        '86097b8a546519529d1df9891aa44b3b',
        '265c26870f5af78b6f30cd4892c29da4',
        'f55bcb9f358faf80518b3275dd766d8b',
        '74aa780e3b5553d0493fb689f4ca6eae',
        '8fc98a88f0db9f44ba9c8072f6739457',
        '188f811c475d63e183bbf1d9b3625c00',
        'c69b2e2bdc2deb47cbb204700d528620',
        'a8246dbf30a4d363fbe5fdc6707bdb49',
        '8b260ae2e5eb83fe57e2d83f04517188',
        '5205fc4af36e1a0d25827957ae7a2271',
        'f376d2e30d3011cb66e369a53b330953',
        'e88a95fb979116b9683944e9dfbb3f72',
        'e40cb50d8937a3a39930c3b0f9fd7eea',
        'e7d71d3a46f1a4f71f830a7535e2d8ca',
        '2a92cf7c09d09274a2f795ae55b2cba6',
        'be3333f8334418445ec64fa026d26b81',
        '92680937e7e9bdec6e49a339d91d638d',
        '0a0f26c60d2971c0abfb9f7be46ca130',
        'bc2819554ad65f60ba9930631809c69d',
        '8b58a8c68b380e59db7332cd3c1e89dd',
        'a96bce819fe193d63fcc35515fb5480d',
        '0100648c2a3ab8ebdb211705f2f1dcac',
        'a915a1f93017bda361287df51a224458',
        'a607fa11b9edf95fc0f1439775206245',
        '6c1a0117fd7c7adaf737e4357b4b21d8',
        'a3c1af1e67b9b9c2d6f5be7f8813e59d',
        '4453fc13e4aad4a400b316b784a96b39',
        '83a5d4e4a32c210f8e4e1c23f9d567d6',
        '6a8cb2f434b40f180008c676737d9300',
        'b2c12dca38ee69a7ccca84c808bb7b57',
        '754908b5345a43b79202dda9597d8fbb',
        '399300e230f874bea4a6407ad19c92ab',
        '06f81dc6889f533dcb1292d6632f9451',
        '79e9876f3e30d68cc49e914feaea30b7',
        '7d933796ef0f1d36c6b2c7ade133c9f1',
        '8a1fb7c8680217beed0691e36c10d9cc',
        'a7219a13cc1b2047f6efde05c53ffe30',
        '19bb9b5922c0ef1b084db0b57c1eef46',
        '8f0eb1c70bef1455cd68118da93e623a',
        '96cd6f9bbeb310c67304423214d1f5ab',
        'ac83579c5149630d2b67fc9145ba334e',
        '046caa8f4f2dbdcc8421e38fcfd7f6dd',
        '739cfa14ea563f6ab241d14db1274b24',
        '55e85bf0a7289688778ddd8d7b07e4a7',
        '579062f5652646747f4a3ef52cd8a205',
        'bd06f0adc9f32bf359312817e30110aa',
        'b276a4d665c4b32ae6d7918dc523a52b',
        'e54ef98594a7bee8abcb95f257eae7e6'
    ];

    public static function getLicenses()
    {
        return static::$licenses;
    }

    public static function valid()
    {
        if(strpos(Environment::get('requestUri'), '/contao/install') !== false)
        {
            return true;
        }

        if (static::$initialized === false)
        {
            static::$valid = EstateManager::checkLicenses(Config::get(static::$key), static::$licenses, static::$key);
            static::$initialized = true;
        }

        return static::$valid;
    }

}
