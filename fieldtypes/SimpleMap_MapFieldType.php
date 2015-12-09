<?php

namespace Craft;

class SimpleMap_MapFieldType extends BaseFieldType {

	public function getName()
	{
		return Craft::t('Map');
	}

	public function defineContentAttribute()
	{
		return AttributeType::Mixed;
	}

	public function getInputHtml($name, $value)
	{
		$id = craft()->templates->formatInputId($name);
		$namespacedId = craft()->templates->namespaceInputId($id);

		$settings = $this->getSettings();

		if (!$settings->lat) $settings->lat = '51.272154';
		if (!$settings->lng) $settings->lng = '0.514951';
		if (!$settings->zoom) $settings->zoom = '15';
		if (!$settings->height) $settings->height = '400';

		craft()->templates->includeJsResource('simplemap/SimpleMap_Map.js');
		craft()->templates->includeJs("new SimpleMap('{$namespacedId}', {lat: '{$settings->lat}', lng: '{$settings->lng}', zoom: '{$settings->zoom}', height: '{$settings->height}'});");

		craft()->templates->includeCssResource('simplemap/SimpleMap_Map.css');

		return craft()->templates->render('simplemap/map-fieldtype', array(
			'id'  => $id,
			'name'  => $name,
			'value' => $value
		));
	}

	protected function defineSettings()
	{
		return array(
			'lat' => array(AttributeType::Mixed, 'min' => 0),
			'lng' => array(AttributeType::Mixed, 'min' => 0),
			'zoom' => array(AttributeType::Number, 'min' => 0),
			'height' => array(AttributeType::Number, 'min' => 100),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('simplemap/map-settings', array(
			'settings' => $this->getSettings()
		));
	}

}