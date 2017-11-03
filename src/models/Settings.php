<?php

namespace ether\simplemap\models;

use craft\base\Model;

class Settings extends Model
{

	public $apiKey = '';
	public $unrestrictedApiKey = '';

	public function rules ()
	{
		return [
			['apiKey', 'required'],
			[['apiKey', 'unrestrictedApiKey'], 'string'],
		];
	}

}