@extends('shopify-app::layouts.default')

<?php
$shop =auth()->user();
  $user_id = $shop->id;
?>

@section('content')
    <!-- You are: (shop domain name) -->
    <!--<p>You are: {{ $shopDomain ?? Auth::user()->name }}</p>-->

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.default.min.css" integrity="sha512-pTaEn+6gF1IeWv3W1+7X7eM60TFu/agjgoHmYhAfLEU8Phuf6JKiiE8YmsNC0aCgQv4192s4Vai8YZ6VNM6vyQ==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" type="text/css" href="{{ url('/css/style.css') }}" />

    <div class="datatable">
        <div class="shop-id" style="display:none">{{ $user_id}}</div>

        <div class="container">
            <div id="exTab1">
                <ul class="nav tabs">
                    <li class="active">
                        <a href="#1a" data-toggle="tab" data-id="1a" class="active">QWQER Settings</a>
                    </li>
                    <li>
                        <a href="#2a" data-toggle="tab" data-id="2a">Shopify Order Details</a>
                    </li>
                    <li>
                        <a href="#3a" data-toggle="tab" data-id="3a">QWQER Delivery Order Details</a>
                    </li>
                </ul>

                <div class="tab-content clearfix">
                    <div class="tab-pane active" id="1a">
                        @include('qwqer_settings')
                    </div>

                    <div class="tab-pane" id="2a">
                        @include('orders')
                    </div>

                    <div class="tab-pane" id="3a">
                        @include('delivery_orders')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <script>
        actions.TitleBar.create(app, {title: 'Welcome'});
    </script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.2/js/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js" integrity="sha512-IOebNkvA/HZjMM7MxL0NYeLYEalloZ8ckak+NDtOViP7oiYzG5vn6WVXyrJDiJPhl4yRdmNAG49iuLmhkUdVsQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $(document).ready(function () {
            utils.getSessionToken(app).then(function () {
                var shopid = $('.shop-id').text();

                var $shipping_rates = $('#shipping_rates').selectize({
                    placeholder: 'Select shipping rates to connect...',
                    plugins: ["clear_button", "remove_button"],
                    delimiter: ",",
                    persist: false,
                    create: function (input) {
                        return {
                            value: input,
                            text: input,
                        };
                    },
                });
                var shipping_rates = $shipping_rates[0].selectize;

                $('#order').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('order-details') }}",
                        type: 'GET',
                        data: {id: shopid}
                    },
                    columns: [
                        {data: 'checkboxes', name: 'checkboxes', orderable: false, searchable: false},
                        {data: 'id', name: 'id', visible: false},
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                        {data: 'order_id', name: 'order_id'},
                        {data: 'customer_name', name: 'customer_name'},
                        {data: 'shipping_address', name: 'shipping_address'},
                        {data: 'billing_address', name: 'billing_address'}
                    ],
                    order: [1, 'desc']
                });

                $('#order_list').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('get-delivery-orders') }}",
                        type: 'GET',
                        data: {id: shopid}
                    },
                    columns: [
                        {data: 'id', name: 'id', visible: false},
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                        {data: 'shop_order_id', name: 'shop_order_id'},
                        {data: 'order_id', name: 'order_id'},
                        {data: 'client_distance', name: 'client_distance'},
                        {data: 'distance', name: 'distance'},
                        {data: 'client_price', name: 'client_price'},
                        {data: 'status', name: 'status'},
                    ],
                    order: [0, 'desc']
                });

                $('body').on('click', '#all_orders', function () {
                    $('input:checkbox[name="order_list"]').prop('checked', $(this).prop('checked'));
                });

                $('body').on('click', 'input:checkbox[name="order_list"]', function () {
                    if ($('input:checkbox[name="order_list"]').length == $('input:checkbox[name="order_list"]:checked').length) {
                        $("#all_orders").prop("checked", true);
                    } else {
                        $("#all_orders").prop('checked', false);
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "{{ url('create-location') }}",
                    data: {shop_id: shopid},
                    dataType: "json",
                    success: function (response) {
                    }
                });

                $.ajax({
                    type: "GET",
                    url: "{{ url('get-orders') }}",
                    data: {shop_id: shopid},
                    dataType: "json",
                    success: function () {
                        var orderTable = $('#order').DataTable();
                        orderTable.ajax.reload();
                    }
                });

                $(document).on('click', '#update_btn', function () {
                    $.ajax({
                        type: "GET",
                        url: "{{ url('get-orders') }}",
                        data: {shop_id: shopid},
                        dataType: "json",
                        success: function () {
                            var orderTable = $('#order').DataTable();
                            orderTable.ajax.reload();
                            $("#all_orders").prop('checked', false);
                            $('input:checkbox[name="order_list"]').prop('checked', false);
                            toastr.info('Orders updated successfully!');
                        }
                    });
                });

                $(document).on('click', '#fulfillment_btn', function () {
                    var ajaxRequests = [];
                    var successfulOrderCount = 0;
                    var failedOrderCount = 0;
                    var orders = [];

                    $('input:checkbox[name="order_list"]:checked').each(function () {
                        var order_val = $(this).val();
                        orders.push(order_val);
                    });

                    var i;
                    for (i = 0; i < orders.length; i++) {
                        var order_id = orders[i];
                        ajaxRequests.push(
                            $.ajax({
                                url: "{{ url('process-order') }}",
                                type: "POST",
                                data: {order_id: order_id, shop_id: shopid},
                                success: function () {
                                    successfulOrderCount++;
                                    var orderTable = $('#order').DataTable();
                                    orderTable.ajax.reload();

                                    var orderListTable = $('#order_list').DataTable();
                                    orderListTable.ajax.reload();
                                },
                                error: function () {
                                    failedOrderCount++;
                                }
                            })
                        );
                    }

                    Promise.all(ajaxRequests).then(function () {
                        if (successfulOrderCount === 1) {
                            toastr.info('Fulfillment sent successfully!');
                        } else if (successfulOrderCount > 1) {
                            toastr.info('Fulfillment of ' + successfulOrderCount + ' orders sent successfully!');
                        }
                        ajaxRequests = [];
                        successfulOrderCount = 0;
                        failedOrderCount = 0;
                    }).catch(function (e) {
                        if (e.responseJSON[0].message === undefined) {
                            toastr.error('Fulfillment failed!');
                        } else {
                            toastr.error('Fulfillment failed! ' + e.responseJSON[0].message);
                        }
                        ajaxRequests = [];
                        successfulOrderCount = 0;
                        failedOrderCount = 0;
                    })
                });

                $.ajax({
                    type: "GET",
                    url: "{{ url('api-details') }}",
                    data: {shop: shopid},
                    dataType: "json",
                    success: function (response) {
                        $("#data_id").val(response.data[0].id);
                        $("#api_key").val(response.data[0].api_key);
                        $("#trading_point_id").val(response.data[0].trading_point_id);
                        $("#order_category").val(response.data[0].order_category);
                        shipping_rates.setValue(response.data[0].shipping_rates);
                    }
                });

                $(document).on('click', '#submitBtn', function () {
                    var id = $('#data_id').val();
                    var api_key = $('#api_key').val();
                    var trading_point_id = $('#trading_point_id').val();
                    var order_category = $('#order_category').val();
                    var shipping_rates = $('#shipping_rates').val();

                    $.ajax({
                        type: "POST",
                        url: "{{ url('update-api-details') }}",
                        data: {
                            shop_id: shopid,
                            id: id,
                            api_key: api_key,
                            trading_point_id: trading_point_id,
                            order_category: order_category,
                            shipping_rates: shipping_rates,
                        },
                        dataType: "json",
                        success: function () {
                            swal("Success", "Data Updated Successfully.");
                        }
                    });
                });
            })
        });
    </script>
@endsection
