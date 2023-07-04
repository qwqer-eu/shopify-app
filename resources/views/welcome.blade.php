@extends('shopify-app::layouts.default')



@section('content')

     <!--You are: (shop domain name) -->

    <!--<p>You are: {{ $shopDomain ?? Auth::user()->name }}</p>-->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">

    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" />

    <link rel="stylesheet" type="text/css" href="{{ url('/css/style.css') }}" />


    
<div class = "datatable">
	<div class="container">
	        <div id="exTab1">
	            <ul class="nav tabs">
	                <li class="active">
	                    <a href="#1a" data-toggle="tab" data-id ="1a" class="active">Shopify Order Details</a>
	                </li>
	                <li>
	                    <a href="#2a" data-toggle="tab" data-id ="2a">QWQER Delivery Order Details</a>
	                </li>
                  <li>
                      <a href="#3a" data-toggle="tab" data-id ="3a">QWQER Settings</a>
                  </li>
	                
	            </ul>

	            <div class="tab-content clearfix">
	                <div class="tab-pane active" id="1a">
	                   @include('orders')
	                </div>

	                <div class="tab-pane" id="2a">
	                  @include('delivery_orders')
	                </div>

                  <div class="tab-pane" id="3a">
                    @include('qwqer_settings')
                  </div>
	               
	             </div>
	        </div>
	    </div>	
    </div>


    

@endsection



@section('scripts')

    @parent

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>


    <script>

        actions.TitleBar.create(app, { title: 'Welcome' });

    </script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>    

	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    







<script>
$(document).ready(function() {

$('#order').DataTable({
              processing: true,
              serverSide: true,
               ajax:{
                    url: "{{ url('get-orders') }}",
                    type: 'GET',
                },
                columns: [
                   {data:'id',name:'id',visible: false},
                   {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false,searchable: false},
                   {data: 'order_id', name: 'order_id'},
                   {data: 'customer_name', name: 'customer_name'},
                   {data: 'customer_email', name: 'customer_email'},
                   {data: 'shipping_address', name: 'shipping_address'},
                   {data: 'billing_address', name: 'billing_address'},
               ]
           });


$('#order_list').DataTable({
              processing: true,
              serverSide: true,
               ajax:{
                    url: "{{ url('get-delivery-orders') }}",
                    type: 'GET',
                },
                columns: [
                   {data:'id',name:'id',visible: false},
                   {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false,searchable: false},
                   {data: 'order_id', name: 'order_id'},
                   {data: 'client_distance', name: 'client_distance'},
                   {data: 'distance', name: 'distance'},
                   {data: 'client_price', name: 'client_price'},
                   {data: 'status', name: 'status'},
               ]
           });







  $.ajax({
    url: "{{ url('create-carrier') }}", 
    type: "POST",
    data:{},
    success:function(data)
    {
      
    }
  });

  // $(document).on('click','#submitBtn',function() {

  //   var id = $('#data_id').val();
  //   var api_key = $('#api_key').val();
  //   var api_url = $('#api_url').val();
  //   var trading_point_id = $('#trading_point_id').val();
  //   var order_category = $('#order_category').val();

  //   $.ajax({
  //     url: "{{ url('api-settings') }}", 
  //     type: "POST",  
  //     data:{
  //       id:id, api_key:api_key, api_url:api_url, trading_point_id:trading_point_id, order_category:order_category
  //     },
  //     dataType:"json",
  //     success:function(data)
  //     {
        
  //     }
  //   });
  // });

  $.ajax({
        type: "GET",
        url: "{{ url('api-details') }}",
        data:{},
        dataType: "json",                   
        success: function(response)
        {  
            $("#data_id").val(response.data[0].id);
            $("#api_key").val(response.data[0].api_key);
            $("#trading_point_id").val(response.data[0].trading_point_id);
            $("#order_category").val(response.data[0].order_category);
        }
    });

  $(document).on('click','#submitBtn',function() {

    var id = $('#data_id').val();
    var api_key = $('#api_key').val();
    var trading_point_id = $('#trading_point_id').val();
    var order_category = $('#order_category').val();

    $.ajax({

      type: "POST",
      url: "{{ url('update-api-details') }}",
      data:{id:id, api_key:api_key, trading_point_id:trading_point_id, order_category:order_category},
      dataType: "json",                   
      success: function(data)
      {  
          
      }
    });
  });

});       

</script>

@endsection