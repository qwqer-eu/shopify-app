@extends('shopify-app::layouts.default')

@section('content')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.default.min.css"
          integrity="sha512-pTaEn+6gF1IeWv3W1+7X7eM60TFu/agjgoHmYhAfLEU8Phuf6JKiiE8YmsNC0aCgQv4192s4Vai8YZ6VNM6vyQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/polaris.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/style.css?ver=2') }}" />

    <div id="header" class="Polaris-Modal-Section">
        <div class="Polaris-Tabs__Wrapper">
            <div class="Polaris-Tabs__ButtonWrapper">
                <ul class="Polaris-Tabs" data-tabs-focus-catchment="true" role="tablist">

                    <li class="Polaris-Tabs__TabContainer" role="presentation">
                        <a id="settings-tab" href="#1a" data-toggle="tab" data-id="1a" class="Polaris-Tabs__Tab Polaris-Tabs__Tab--active" style="text-decoration: none">
                            <span class="Polaris-InlineStack" style="--pc-inline-stack-align: center; --pc-inline-stack-block-align: center; --pc-inline-stack-wrap: nowrap; --pc-inline-stack-gap-xs: var(--p-space-200); --pc-inline-stack-flex-direction-xs: row;">
                                <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium" style="font-size:var(--p-text-heading-md-font-size); padding: var(--p-space-150) var(--p-space-300);">
                                    Settings
                                </span>
                            </span>
                        </a>
                    </li>

                    <li class="Polaris-Tabs__TabContainer" role="presentation">
                        <a id="orders-tab" href="#2a" data-toggle="tab" data-id="2a" class="Polaris-Tabs__Tab" style="text-decoration: none">
                            <span class="Polaris-InlineStack" style="--pc-inline-stack-align: center; --pc-inline-stack-block-align: center; --pc-inline-stack-wrap: nowrap; --pc-inline-stack-gap-xs: var(--p-space-200); --pc-inline-stack-flex-direction-xs: row;">
                                <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium" style="font-size:var(--p-text-heading-md-font-size); padding: var(--p-space-150) var(--p-space-300);">
                                    Orders
                                </span>
                            </span>
                        </a>
                    </li>

                </ul>
            </div>
        </div>

        <div id="primary_actions" class="Polaris-Page-Header__RightAlign" style="display: none;">
            <div class="Polaris-Page-Header__PrimaryActionWrapper">

                <button id="update_btn" type="button"
                        class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantSecondary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter Polaris-Button--iconWithText">
                    <span class="Polaris-Button__Icon">
                        <span class="Polaris-Icon">
                            <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.1389 3.88962L13.2089 3.99961C13.236 3.99964 13.2629 3.99416 13.2878 3.98349C13.3127 3.97282 13.3353 3.95724 13.354 3.93759C13.3727 3.91795 13.3872 3.89467 13.3966 3.86923C13.4061 3.8438 13.4103 3.81674 13.4089 3.78964L13.1489 0.729586C13.1473 0.692028 13.1346 0.655897 13.1123 0.625582C13.0901 0.595266 13.0593 0.572145 13.024 0.559298C12.9887 0.546451 12.9504 0.544362 12.9138 0.553316C12.8773 0.562271 12.8443 0.581855 12.8189 0.609591L10.0089 3.60959C9.99411 3.63587 9.98571 3.66538 9.98452 3.69553C9.98333 3.72567 9.9894 3.75569 10.0021 3.78305C10.0148 3.81042 10.0338 3.83433 10.0576 3.85288C10.0814 3.87142 10.1093 3.88398 10.1389 3.88962Z" fill="#586DB3"/>
                                <path d="M11.7787 12.1899C12.9804 11.0858 13.7585 9.59663 13.9788 7.97977L13.1688 7.8798C12.9999 9.13756 12.4443 10.3117 11.5787 11.2398C10.7442 12.1406 9.66146 12.7739 8.46716 13.0595C7.27286 13.345 6.02068 13.27 4.86896 12.844C3.71724 12.4181 2.71768 11.6602 1.99658 10.6663C1.27549 9.67236 0.865265 8.48697 0.817749 7.25992C0.770233 6.03288 1.08754 4.81935 1.72961 3.77262C2.37169 2.72589 3.30966 1.89305 4.42499 1.37931C5.54032 0.865567 6.78292 0.693934 7.99573 0.886268C9.20853 1.0786 10.3371 1.62627 11.2388 2.45988L11.7888 1.86979C10.7873 0.951742 9.5403 0.344958 8.19989 0.12345C6.85948 -0.0980571 5.48357 0.0753097 4.23999 0.622352C2.99641 1.16939 1.93889 2.06651 1.19641 3.20426C0.453932 4.34201 0.0585938 5.67124 0.0585938 7.02982C0.0585938 8.38841 0.453932 9.71763 1.19641 10.8554C1.93889 11.9931 2.99641 12.8903 4.23999 13.4373C5.48357 13.9843 6.85948 14.1577 8.19989 13.9362C9.5403 13.7147 10.7873 13.1079 11.7888 12.1899H11.7787Z" fill="#586DB3"/>
                                <path d="M13.618 7.53963C13.5654 7.53264 13.5119 7.53614 13.4607 7.55001C13.4095 7.56387 13.3616 7.58787 13.3197 7.62044C13.2778 7.65301 13.2428 7.69361 13.2168 7.73983C13.1908 7.78605 13.1741 7.83691 13.168 7.88961L13.978 7.98958C13.9892 7.8823 13.9578 7.77499 13.8904 7.69075C13.823 7.60652 13.7251 7.5522 13.618 7.53963Z" fill="#586DB3"/>
                            </svg>
                        </span>
                    </span>
                    <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">
                        Update Orders
                    </span>
                </button>

                <button id="process-selected-button" type="button"
                        class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantPrimary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter">
                    <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">Process</span>
                </button>

            </div>
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="tab-pane active" id="1a">
            @include('qwqer_settings')
        </div>

        <div class="tab-pane" id="2a">
            @include('orders')
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
            integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
            crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
            integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.2/js/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js"
            integrity="sha512-IOebNkvA/HZjMM7MxL0NYeLYEalloZ8ckak+NDtOViP7oiYzG5vn6WVXyrJDiJPhl4yRdmNAG49iuLmhkUdVsQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $(document).ready(function () {
            $('#settings-tab').on('click', function () {
                $(this).addClass('Polaris-Tabs__Tab--active');
                $('#orders-tab').removeClass('Polaris-Tabs__Tab--active');
                $('#primary_actions').hide();
            });
            $('#orders-tab').on('click', function () {
                $(this).addClass('Polaris-Tabs__Tab--active');
                $('#settings-tab').removeClass('Polaris-Tabs__Tab--active');
                $('#primary_actions').show();
            });

            $('#order_category').on('change', function () {
                $('#order_category_selected').text($(this).find('option:selected').text());
            });
            $('#api').on('change', function () {
                $('#api_selected').text($(this).find('option:selected').text());
            });

            utils.getSessionToken(app).then(function () {
                @if($is_carrier_service_available ?? false)
                var $carrier_service_shipping_rates = $('#carrier_service_shipping_rates').selectize({
                    placeholder: 'Select shipping rates to connect...',
                    plugins: ["clear_button", "remove_button"],
                    delimiter: ",",
                    persist: false,
                    dropdownParent: "body",
                    create: function (input) {
                        return {
                            value: input,
                            text: input,
                        };
                    },
                });
                var carrier_service_shipping_rates = $carrier_service_shipping_rates[0].selectize;
                @endif

                var $shipping_rates = $('#shipping_rates').selectize({
                    placeholder: 'Select shipping rates to connect...',
                    plugins: ["clear_button", "remove_button"],
                    delimiter: ",",
                    persist: false,
                    dropdownParent: "body",
                    create: function (input) {
                        return {
                            value: input,
                            text: input,
                        };
                    },
                });
                var shipping_rates = $shipping_rates[0].selectize;

                var orderTable = $('#order');

                orderTable.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('order-details') }}",
                        type: 'GET'
                    },
                    columns: [
                        {data: 'checkboxes', name: 'checkboxes', orderable: false, searchable: false},
                        {data: 'id', name: 'id', visible: false},
                        {data: 'order_id', name: 'order_id'},
                        {data: 'date', name: 'date'},
                        {data: 'delivery_type', name: 'delivery_type'},
                        {data: 'shipping_address', name: 'shipping_address'},
                        {data: 'status', name: 'status'},
                        {data: 'actions', name: 'actions', orderable: false, searchable: false}
                    ],
                    searchDelay: 1000,
                    order: [2, 'desc'],
                    lengthMenu: [
                        [20, 50, -1],
                        [20, 50, 'All']
                    ],
                    oLanguage: {
                        sInfo: 'Showing _START_ to _END_ of _TOTAL_ orders',
                        sInfoEmpty: 'No orders found',
                        sLengthMenu: 'Show _MENU_ orders',
                        oPaginate: {
                            sFirst: "«",
                            sLast: "»",
                            sNext: ">",
                            sPrevious: "<"
                        },
                    }
                });

                orderTable.on('draw.dt', function () {
                    $(".process-order-button").each(function () {
                        $(this).click(function () {
                            $.ajax({
                                url: "{{ url('process-order') }}",
                                type: "POST",
                                data: {order_id: $(this).attr('id')},
                                success: function () {
                                    orderTable.DataTable().ajax.reload();
                                    toastr.info('Order successfully processed!');
                                },
                                error: function (e) {
                                    if (e.responseJSON.message === undefined) {
                                        toastr.error('Failed to process order!');
                                    } else {
                                        toastr.error('Failed to process order! ' + e.responseJSON.message);
                                    }
                                }
                            });
                        });
                    });
                });

                $('body').on('click', '#all_orders', function () {
                    $('input:checkbox[name="order_list"]').prop('checked', $(this).prop('checked'));
                });

                $('body').on('click', 'input:checkbox[name="order_list"]', function () {
                    if ($('input:checkbox[name="order_list"]').length === $('input:checkbox[name="order_list"]:checked').length) {
                        $("#all_orders").prop("checked", true);
                    } else {
                        $("#all_orders").prop('checked', false);
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "{{ url('create-location') }}",
                    dataType: "json",
                    success: function (response) {
                    },
                    error: function (e) {
                        toastr.error('Failed to save shop locations! ' + e.responseJSON.message);
                    }
                });

                $.ajax({
                    type: "GET",
                    url: "{{ url('get-orders') }}",
                    dataType: "json",
                    success: function () {
                        orderTable.DataTable().ajax.reload();
                    },
                    error: function (e) {
                        toastr.error('Failed to get orders! ' + e.responseJSON.message);
                    }
                });

                $(document).on('click', '#update_btn', function () {
                    $.ajax({
                        type: "GET",
                        url: "{{ url('get-orders') }}",
                        dataType: "json",
                        success: function () {
                            orderTable.DataTable().ajax.reload();
                            $("#all_orders").prop('checked', false);
                            $('input:checkbox[name="order_list"]').prop('checked', false);
                            toastr.info('Orders updated successfully!');
                        },
                        error: function (e) {
                            toastr.error('Failed to update orders! ' + e.responseJSON.message);
                        }
                    });
                });

                $(document).on('click', '#process-selected-button', function () {
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
                                data: {order_id: order_id},
                                success: function () {
                                    successfulOrderCount++;
                                    orderTable.DataTable().ajax.reload();
                                },
                                error: function () {
                                    failedOrderCount++;
                                }
                            })
                        );
                    }

                    Promise.all(ajaxRequests).then(function () {
                        if (successfulOrderCount === 1) {
                            toastr.info('Order successfully processed!');
                        } else if (successfulOrderCount > 1) {
                            toastr.info(successfulOrderCount + ' orders successfully processed!');
                        }
                        ajaxRequests = [];
                        successfulOrderCount = 0;
                        failedOrderCount = 0;
                    }).catch(function (e) {
                        if (e.responseJSON.message === undefined) {
                            toastr.error('Failed to process order!');
                        } else {
                            toastr.error('Failed to process order! ' + e.responseJSON.message);
                        }
                        ajaxRequests = [];
                        successfulOrderCount = 0;
                        failedOrderCount = 0;
                    })
                });

                $.ajax({
                    type: "GET",
                    url: "{{ url('api-details') }}",
                    dataType: "json",
                    success: function (response) {
                        $("#api").val(response.data.api);
                        $('#api_selected').text(response.data.api);
                        $("#api_key").val(response.data.api_key);
                        $("#trading_point_id").val(response.data.trading_point_id);
                        $("#order_category").val(response.data.order_category);
                        $('#order_category_selected').text(response.data.order_category);
                        @if($is_carrier_service_available ?? false)
                        carrier_service_shipping_rates.setValue(response.data.carrier_service_shipping_rates);
                        @endif
                        shipping_rates.setValue(response.data.shipping_rates);
                    },
                    error: function (e) {
                        toastr.error('Failed to get settings! ' + e.responseJSON.message);
                    }
                });

                $(document).on('click', '#submitBtn', function () {
                    var api = $('#api').val();
                    var api_key = $('#api_key').val();
                    var trading_point_id = $('#trading_point_id').val();
                    var order_category = $('#order_category').val();
                    @if($is_carrier_service_available ?? false)
                    var carrier_service_shipping_rates = $('#carrier_service_shipping_rates').val();
                    @endif
                    var shipping_rates = $('#shipping_rates').val();

                    $.ajax({
                        type: "POST",
                        url: "{{ url('update-api-details') }}",
                        data: {
                            api: api,
                            api_key: api_key,
                            trading_point_id: trading_point_id,
                            order_category: order_category,
                            @if($is_carrier_service_available ?? false)
                            carrier_service_shipping_rates: carrier_service_shipping_rates,
                            @endif
                            shipping_rates: shipping_rates,
                        },
                        dataType: "json",
                        success: function () {
                            swal("Success", "Settings updated successfully.");
                            orderTable.DataTable().ajax.reload();
                        },
                        error: function (e) {
                            toastr.error('Failed to update settings! ' + e.responseJSON.message);
                        }
                    });
                });
            })
        });
    </script>
@endsection
