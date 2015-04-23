<?php

class PaymentLibrariesSeeder extends Seeder
{

	public function run()
	{
		Eloquent::unguard();

		$gateways = [
			array('name'=>'BeanStream', 'provider'=>'BeanStream', 'payment_library_id' => 2),
			array('name'=>'Psigate', 'provider'=>'Psigate', 'payment_library_id' => 2)
		];
		
		foreach ($gateways as $gateway)
		{
			Gateway::create($gateway);
		}

	}
}
