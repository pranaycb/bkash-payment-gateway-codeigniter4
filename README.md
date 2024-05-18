# bkash-payment-gateway-codeigniter4
A Codeifniter4 Library for Bkash Tokenized Payment Gateway Api

## Installation

Download the BkashPhp.php file and put the file in App/Libraries folder. As simple as it is

## Usage

Create New Payment

```php

use App\Libraries\BkashPhp;

$paymentData = [
  'mode' => '0011',
  'payerReference' => 'Reference',
  'callbackURL' => url_to('route.bkash.execute'),
  'amount' => 100,
  'currency' => 'BDT',
  'intent' => 'sale',
  'merchantInvoiceNumber' => 'Invoie Numver',
];

try {

  $bkashphp = new BkashPhp();

  $bkashphp->setConfig([
    'app_key' => 'Bkash App Key',
    'app_secret' => 'Bkash App Secret',
    'username' => 'Bkash Username',
    'password' => 'Bkash Password',
    'environment' => 'sandbox', //sandbox or production
  ]);

  $response = $bkashphp->createPayment($paymentData);

  return redirect()->to($response->bkashURL)->withCookies(); //redirect with cookies
}

//Handle error
catch (\Exception $e) {
  echo $e->getMessage();
}
```
---
Execure Payment
```php
use App\Libraries\BkashPhp;

try {

  $paymentID = $this->request->getGet('paymentID');

  $bkashphp = new BkashPhp();

  $bkashphp->setConfig([
    'app_key' => 'Bkash App Key',
    'app_secret' => 'Bkash App Secret',
    'username' => 'Bkash Username',
    'password' => 'Bkash Password',
    'environment' => 'sandbox', //sandbox or production
  ]);

  $response = $bkashphp->executePayment($paymentID);

   if ($response->transactionStatus != 'Completed') {

      return redirect()->route('route.bkash.status')->with('error', $response->errorMessage);
   }

   return redirect()->route('route.bkash.status')->with('success', "Bkash payment successful");
}

//Handle error
catch (\Exception $e) {
  echo $e->getMessage();
}

```

## Need Help?

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

If you have any problem regarding the file please feel free to contact me via email pranaycb.ctg@gmail.com

Happy Coding 🤗🤗

## License

[MIT](https://choosealicense.com/licenses/mit/)
