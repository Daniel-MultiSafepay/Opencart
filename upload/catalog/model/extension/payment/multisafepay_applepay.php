<?php
/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before you update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      TechSupport <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2017 MultiSafepay, Inc. (http://www.multisafepay.com)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
class ModelExtensionPaymentMultiSafePayApplepay extends Model
{
    const MAX_PAYMENT_METHOD_LENGTH = 170;

    public function getMethod($address, $total)
    {
        if ($total == 0) {
            return false;
        }

        $this->load->language('extension/payment/multisafepay');

        $totalcents = $total * 100;

        if ($total) {
            if ($this->config->get('payment_multisafepay_applepay_min_amount') && $totalcents < $this->config->get('payment_multisafepay_applepay_min_amount')) {
                return false;
            }
            if ($this->config->get('payment_multisafepay_applepay_max_amount') && $totalcents > $this->config->get('payment_multisafepay_applepay_max_amount')) {
                return false;
            }
        }


        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('payment_multisafepay_applepay_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('payment_multisafepay_applepay_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $hideApplePayScript = "Only for Apple devices
            <script type='text/javascript' id='msp_applepayscript'>
                var applePayBlock = document.querySelector('input[value=\"multisafepay_applepay\"]').parentElement.parentElement;
                applePayBlock.style.display = 'none';
                try {
                    if (window.ApplePaySession && window.ApplePaySession.canMakePayments()) {
                        applePayBlock.style.display = 'block';
                    }
                } catch (error) {
                    console.warn('MultiSafepay error when trying to initialize Apple Pay:', error);
                }
            </script>";

            $method_data = array(
                'code' => 'multisafepay_applepay',
                'title' => $this->getTitle(),
                'terms' => $hideApplePayScript,
                'sort_order' => $this->config->get('payment_multisafepay_applepay_sort_order')
            );
        }

        return $method_data;
    }

    private function getTitle()
    {
        $title = $this->language->get('text_title_applepay');
        if (!$this->config->get('payment_multisafepay_use_payment_logo')){
            return $title;
        }
        $baseUrl = $this->request->server['HTTPS'] ? $this->config->get('config_ssl') : $this->config->get('config_url');
        $logo = '<img height=32 src="' . $baseUrl . 'catalog/view/theme/default/image/multisafepay/applepay.svg" alt="applepay"/>';
        $titleWithLogo = $logo . '  ' . $title;
        if (mb_strlen($titleWithLogo) > self::MAX_PAYMENT_METHOD_LENGTH) {
            return $title;
        }
        return $titleWithLogo;
    }
}

?>
