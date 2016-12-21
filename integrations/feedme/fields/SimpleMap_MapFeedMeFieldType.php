<?php
namespace Craft;

class SimpleMap_MapFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'simplemap/_integrations/feedme/fields/simplemap_map';
    }
    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        // Initialize content array
        $content = array();

        foreach ($data as $subfieldHandle => $subfieldData) {
            // Set value to subfield of correct address array
            $content[$subfieldHandle] = $subfieldData;
        }

        // In order to full-fill any empty gaps in data (lng/lat/address), we check to see if we have any data missing
        // then, request that data through Google's geocoding API - making for a hands-free import. 
        
        // Check for empty Address
        if (!isset($content['address'])) {
            $content['address'] = $this->_getAddressFromLatLng($content['lat'], $content['lng']);
        }

        // Check for empty Longitude/Latitude
        if (!isset($content['lat']) || !isset($content['lng'])) {
            $latlng = $this->_getLatLngFromAddress($content['address']);
            $content['lat'] = $latlng['lat'];
            $content['lng'] = $latlng['lng'];
        }

        // Return data
        return $content;
    }




    // Private Methods
    // =========================================================================

    private function _getLatLngFromAddress($address)
    {
        $this->settings = craft()->plugins->getPlugin('SimpleMap')->getSettings();

        if (!$this->settings['browserApiKey']) return null;

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . rawurlencode($address)
            . '&key=' . $this->settings['browserApiKey'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = json_decode(curl_exec($ch), true);

        if (array_key_exists('error_message', $resp) && $resp['error_message'])
            SimpleMapPlugin::log($resp['error_message'], LogLevel::Error);

        if (empty($resp['results'])) return null;

        return $resp['results'][0]['geometry']['location'];
    }

    private function _getAddressFromLatLng($lat, $lng)
    {
        $this->settings = craft()->plugins->getPlugin('SimpleMap')->getSettings();

        if (!$this->settings['browserApiKey']) return null;

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . rawurlencode($lat) . ',' . rawurlencode($lng)
            . '&key=' . $this->settings['browserApiKey'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = json_decode(curl_exec($ch), true);

        if (array_key_exists('error_message', $resp) && $resp['error_message'])
            SimpleMapPlugin::log($resp['error_message'], LogLevel::Error);

        if (empty($resp['results'])) return null;

        return $resp['results'][0]['formatted_address'];
    }
    
}