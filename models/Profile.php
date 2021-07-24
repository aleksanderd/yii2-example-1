<?php
namespace app\models;

use dektrium\user\models\Profile as BaseProfile;

/**
* @property string $phone
* @property string $company
*/
class Profile extends BaseProfile
{
	public function rules()
	{
		return array_merge(parent::rules(), [
			[['company'], 'string'],
			[['phone'], 'string', 'min' => 5, 'max' => 12],
		]);
	}	
}