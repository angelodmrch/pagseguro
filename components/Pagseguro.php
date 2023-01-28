<?php namespace Dmrch\PagSeguro\Components;

use Cms\Classes\ComponentBase;
use Dmrch\PagSeguro\Models\Pagseguro as PagseguroModel;
use Dmrch\PagSeguro\Models\Settings;
use RainLab\User\Models\User as ModelUsers;
use Redirect;
use Session;
use Auth;
use Http;
use Request;
use Validator;

class Pagseguro extends ComponentBase
{




    public function componentDetails()
    {
        return [
            'name'        => 'Pagseguro',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'successPage' => [
                 'title'             => 'successPage',
                 'default'           => 'sucesso',
                 'type'              => 'string'
            ]
        ];
    }

    public function onRun() {
        if (Session::get('xml')) {

            $xml = json_decode(Session::get('xml'));
            $this->page['referencia'] = $xml->reference;
            $this->page['payment_link'] =  $xml->paymentLink;

            //Session::forget('xml');

        }
    }

    protected function credentials(){
        $env = Settings::get('env');

        if ($env == 'production') {
            return array(
                'env' => $env,
                'email' => Settings::get('email'),
                'token' => Settings::get('token_production'),
                'endereco' => ''
            );
        } else {
            return array(
                'env' => $env,
                'email' => Settings::get('email'),
                'token' => Settings::get('token_sandbox'),
                'endereco' => 'sandbox.'
            );
        }
    }  

    /**
     * param $items Array obj(id,name,price)
     * return Redirect for checkout
     */
    public function onCheckout(){

        $rules = [
            'senderName' => 'required',
            'senderCPF' => 'required',
            'senderAreaCode' => 'required',
            'senderPhone' => 'required',
            'senderEmail' => 'required|email',
            
            'senderHash' => 'required',
            'paymentMethod' => 'required',

            'bankName' => 'required_if:paymentMethod,eft',

            'brand' => 'required_if:paymentMethod,creditCard',
            'cardNumber' => 'required_if:paymentMethod,creditCard',
            'cvv' => 'required_if:paymentMethod,creditCard',
            'expirationMonth' => 'required_if:paymentMethod,creditCard',
            'expirationYear' => 'required_if:paymentMethod,creditCard',
            'creditCardHolderName' => 'required_if:paymentMethod,creditCard',
            'creditCardHolderCPF' => 'required_if:paymentMethod,creditCard',
            'creditCardHolderBirthDate' => 'required_if:paymentMethod,creditCard',
            'installmentQuantity' => 'required_if:paymentMethod,creditCard',
        ];

        $messages = [
            'senderName.required' => 'Este campo é obrigatório.',
   
        ];

        $validation = Validator::make(post(), $rules, $messages);    

        if ($validation->fails()) {
            
   
            return Redirect::to($this->page->url)->withErrors($validation->messages())->withInput(post());


        }else{

            try {

                if (post('user_id') == "") {

                    $formUser = new ModelUsers();
                    $formUser->name = post('senderName');
                    $formUser->email = post('senderEmail');
                    $formUser->password = str_slug(post('senderPhone'));
                    $formUser->password_confirmation = str_slug(post('senderPhone'));
                    $formUser->is_activated = 1;                
                    $formUser->save();      

                } else {
                   $formUser = ModelUsers::find(post('user_id'));
                }       

                $pagseguro = new PagseguroModel;
                $pagseguro->user_id = $formUser->id;
                $pagseguro->transaction_id = '';
                $pagseguro->items = Session::get('cart_items');
                $pagseguro->total = Session::get('cart_total');
                $pagseguro->save();

                $data = array('senderName' => $_POST['senderName'],
                    'senderCPF' => $_POST['senderCPF'],
                    'senderAreaCode' => $_POST['senderAreaCode'],
                    'senderPhone' => $_POST['senderPhone'],
                    'senderEmail' => $_POST['senderEmail'],
                    'senderHash' => $_POST['senderHash'],
                    'paymentMode' => 'default',
                    'notificationURL' =>  Request::getBaseUrl().'/pagseguro/notification',
                    'reference' => $pagseguro->id,
                    'paymentMethod' => $_POST['paymentMethod'],              

                    'receiverEmail' => $this->credentials()['email'],
                    'currency' => 'BRL',
                    'extraAmount' => '0.00',
                    'shippingAddressRequired' => 'False',  
                );


                foreach (array_values(Session::get('cart_items')) as $key => $value) {   
                    $data['itemId'.$key] = $value->id;
                    $data['itemDescription'.$key] = $value->name;
                    $data['itemAmount'.$key] = number_format($value->price, 2, '.', '');
                    $data['itemQuantity'.$key] = $value->quantity;
                }

                if ($_POST['paymentMethod'] == 'eft') {
                    $data['bankName'] = $_POST['bankName'];
                }

                if ($_POST['paymentMethod'] == 'creditCard') {

                    $data['creditCardToken'] = $_POST['creditCardToken'];
                    $data['installmentQuantity'] = trim(explode('|', $_POST['installmentQuantity'])[0]);
                    $data['installmentValue'] = trim((string)number_format(explode('|', $_POST['installmentQuantity'])[1], 2, '.', ''));
                    $data['noInterestInstallmentQuantity'] = '2';
                    $data['creditCardHolderName'] = $_POST['creditCardHolderName'];
                    $data['creditCardHolderCPF'] = $_POST['creditCardHolderCPF'];
                    $data['creditCardHolderBirthDate'] = $_POST['creditCardHolderBirthDate'];

                    $data['billingAddressStreet'] = 'Av. Brig. Faria Lima';
                    $data['billingAddressNumber'] = '1384';
                    $data['billingAddressComplement'] = '5o andar';
                    $data['billingAddressDistrict'] = 'Jardim Paulistano';
                    $data['billingAddressPostalCode'] = '01452002';
                    $data['billingAddressCity'] = 'Sao Paulo';
                    $data['billingAddressState'] = 'SP';
                    $data['billingAddressCountry'] = 'BRA';

                }

                $retorno = Http::post("https://ws.". $this->credentials()['endereco'] ."pagseguro.uol.com.br/v2/transactions?email=" . $this->credentials()['email'] . "&token=" . $this->credentials()['token'], function($http) use ($data){
                    $http->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8"));
                    $http->setOption(CURLOPT_POST, 1);
                    $http->setOption(CURLOPT_SSL_VERIFYPEER, false);
                    $http->setOption(CURLOPT_RETURNTRANSFER, true);

                    $http->data($data);

                });            
                
                $xml = simplexml_load_string($retorno);

                $pagseguro->transaction_id = $xml->code;
                $pagseguro->status = $xml->status;
                $pagseguro->payment_link = $xml->paymentLink;
                $pagseguro->save();

                Session::put('xml', json_encode($xml));

                return Redirect::to($this->property('successPage'));

            } catch (\Exception $error) { 
                return $error->getMessage(); 
            }

        }
    }


    public function onNotification(){
        try {

            $retorno = Http::get("https://ws.". $this->credentials()['endereco'] ."pagseguro.uol.com.br/v3/transactions/notifications/". $_POST['notificationCode'] ."?email=" . $this->credentials()['email'] . "&token=" . $this->credentials()['token'], function($http){
                $http->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8"));
                $http->setOption(CURLOPT_SSL_VERIFYPEER, false);
                $http->setOption(CURLOPT_RETURNTRANSFER, true);
            });
           
            $xml = simplexml_load_string($retorno);

            $pagseguro = PagseguroModel::where('transaction_id', $xml->code)->first();
            $pagseguro->status = $xml->status;
            $pagseguro->save();

            print_r($xml);
            exit;

         
        } catch (Exception $error) { 
            echo $error->getMessage(); 
        }
    }

    public static function getStatus($code){
        switch($code){
            case '0':
            return 'Inicializado';
            case '1':
            return 'Aguardando pagamento';
            case '2':
            return 'Em análise';
            case '3':
            return 'Paga';
            case '4':
            return 'Disponível';
            case '5':
            return 'Em disputa';
            case '6':
            return 'Devolvida';
            case '7':
            return 'Cancelada';
        }
    }

    public function onGetSession() {

        $retorno = Http::post("https://ws.". $this->credentials()['endereco'] ."pagseguro.uol.com.br/v2/sessions?email=" . $this->credentials()['email'] . "&token=" . $this->credentials()['token'], function($http){
            $http->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8"));
            $http->setOption(CURLOPT_POST, 1);
            $http->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $http->setOption(CURLOPT_RETURNTRANSFER, true);
        });        
        
        $xml = simplexml_load_string($retorno);

        return json_encode($xml);
    }

    public function getCartTotal() {
        return Session::get('cart_total');
    }

    public function getNoInterest() {
        if (Settings::get('no_interest')) {
            return Settings::get('no_interest');
        } else {
            return 2;
        }
    }
}
