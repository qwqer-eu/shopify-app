<div class="input-name">
    <input type="hidden" id="data_id" value="">

    <div class="field">
        <label>API Key</label>
        <input type="text" id="api_key" value="">
    </div>

    <div class="field">
        <label>Trading point id</label>
        <input type="text" id="trading_point_id" value="">
    </div>

    <div class="field">
        <label>Order Category</label>
        <select id="order_category">
            <option value="Flowers">Flowers</option>
            <option value="Food">Food</option>
            <option value="Cake">Cake</option>
            <option value="Present">Present</option>
            <option value="Clothes">Clothes</option>
            <option value="Document">Document</option>
            <option value="Jewelry">Jewelry</option>
        </select>
    </div>

    <div class="field">
        <label>Shipping rates</label>
        <select id="shipping_rates" multiple>
            @foreach($selectable_shipping_rates as $shipping_zone => $shipping_rates)
                <optgroup label="{{$shipping_zone}}">
                    @foreach($shipping_rates as $shipping_rate_name)
                        <option value="{{ $shipping_rate_name }}">{{ $shipping_rate_name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>

    <div class="submit-button">
        <button id='submitBtn'>Update</button>
    </div>
</div>
