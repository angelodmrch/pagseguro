$('#brand').change(function(){ createCardToken();getInstallments(); });
$('#cardNumber').change(function(){ createCardToken();getInstallments(); });
$('#cvv').change(function(){ createCardToken() });
$('#expirationMonth').change(function(){ createCardToken() });
$('#expirationYear').change(function(){ createCardToken() });

$(document).ready(function(){
	createCardToken();
	getInstallments();
});

$.request('PagSeguro::onGetSession', {
	success: function(retorno) {
		var session_id =  JSON.parse(retorno.result)['id'];
		PagSeguroDirectPayment.setSessionId(session_id);

		PagSeguroDirectPayment.getPaymentMethods({
			amount: $('#cart_amount').val(),
		    success: function(response) {
		        var banks = response.paymentMethods.ONLINE_DEBIT.options;

		        var op = '';
		        for(var bank of Object.entries(banks)) {
		        	if ( bank[1].status == 'AVAILABLE' ) {
		        		op += '<option value="'+bank[1].name+'">'+bank[1].displayName+'</option>'
		        	}
		        }

		        $('#bankName').html(op);

		    },
		    error: function(response) {
		        console.log(response);
		    }
		});

		PagSeguroDirectPayment.onSenderHashReady(function(response){
		    if(response.status == 'error') {
		        console.log(response.message);
		        return false;
		    }
		    var hash = response.senderHash;
		    $('#senderHash').val(hash);
		});

	}
}); 

function setPaymentMethod(method) {
	$('.pmethod').hide();
	$('.'+method).show();
	$('#paymentMethod').val(method);
}

//GetBrand function
$('#cardNumber').on('keyup', function(e) {	    
    var value = $(this).val().trim();

    if(value.length == 6) {
        PagSeguroDirectPayment.getBrand({
		    cardBin: value,
		    success: function(response) {
		     	$('#brand').val(response.brand.name)
		    },
		    error: function(response) {
		     	console.log(response);
		    }
		});
    }	    
});


function getInstallments() {
	if ( $('#brand').val() ) {
		PagSeguroDirectPayment.getInstallments({
	        amount: $('#cart_amount').val(),
	        maxInstallmentNoInterest: $('#cart_nointerest').val(),
	        brand: $('#brand').val(),
	        success: function(response) {
	          	var installments = response.installments;
	          	installments = installments[Object.keys(installments)[0]];

		        var op = '';
		        for(var installment of Object.entries(installments)) {			        	
		        	op += '<option value="'+installment[1].quantity+'|'+ installment[1].installmentAmount +'">'+installment[1].quantity +"x R$ "+ number_format(installment[1].installmentAmount,2,',','.') +" (R$ "+ number_format(installment[1].totalAmount,2,',','.')+')</option>' 
			        }

		        $('#installmentQuantity').html(op);
		        setinstallmentAmount();
	       	},
	        error: function(response) {
	            console.log(response);
	       	}
		});
	}
}

function createCardToken() {
	if ( $('#brand').val() &&
		$('#cardNumber').val() &&
		$('#cvv').val() &&
		$('#expirationMonth').val() &&
		$('#expirationYear').val()) {

		PagSeguroDirectPayment.createCardToken({
		   	cardNumber: $('#cardNumber').val(),
		   	brand: $('#brand').val(),
		   	cvv: $('#cvv').val(),
		   	expirationMonth: $('#expirationMonth').val(),
		   	expirationYear: $('#expirationYear').val(),
		   	success: function(response) {
		        $('#creditCardToken').val(response.card.token);
		   	},
		   	error: function(response) {
		        console.log(response);
		   	}
		});
	}
}
	


 function number_format (number, decimals, decPoint, thousandsSep) {


  number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
  var n = !isFinite(+number) ? 0 : +number
  var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
  var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
  var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
  var s = ''

  var toFixedFix = function (n, prec) {
    if (('' + n).indexOf('e') === -1) {
      return +(Math.round(n + 'e+' + prec) + 'e-' + prec)
    } else {
      var arr = ('' + n).split('e')
      var sig = ''
      if (+arr[1] + prec > 0) {
        sig = '+'
      }
      return (+(Math.round(+arr[0] + 'e' + sig + (+arr[1] + prec)) + 'e-' + prec)).toFixed(prec)
    }
  }

  // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.')
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || ''
    s[1] += new Array(prec - s[1].length + 1).join('0')
  }

  return s.join(dec)
}