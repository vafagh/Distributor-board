<div class="modal-body">
    <div class="form-group row">
        <div class="col-12 col-md-6">
            <label for="name" class="col-form-label">Location Name</label>
            <input name="name" class="form-control" type="text" value="{{$object->name}}" required>
        </div>
        <div class="col-12 col-md-6">
            <label for="person" class="col-form-label">Contact Person</label>
            <input name="person" class="form-control" type="text" value="{{$object->person}}" required>
        </div>
    </div>

    <div class="form-group">
        <label for="longName" class="col-form-label">Long Name</label>
        <input name="longName" class="form-control" type="text" value="{{$object->longName}}">
    </div>

    <div class="form-row">
        @if ($object->image!='')
            <div class="currentimg col-6 row">
                <div class="col-4">
                    <img class="w-100" src="/img/location/{{$object->image}}" alt="">
                </div>
                <div class="col-8">
                    <input type="checkbox" name="clearimg" id="clearimg">
                    <label class="file-label" for="clearimg">Clear</label>
                </div>
            </div>
        @endif
        <div class="col-6">
            <input name="image" type="file" class="file-input">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-7">
            <label for="phone" class="col-form-label">Phone</label>
            <input name="phone" class="form-control" type="text" value="{{$object->phone}}" required>
        </div>
        <div class="col-5 row">

            <label for="type" class="col-form-label">Type:</label>
            <select class="form-control" name="type">
                <option {{($object->type == 'Client') ? 'selected' : ''}} value="Client">Client</option>
                <option {{($object->type == 'Warehouse') ? 'selected' : ''}} value="Warehouse">Warehouse - pickup</option>
                <option {{($object->type == 'DropOff') ? 'selected' : ''}} value="DropOff">Warehouse - drop off</option>
                <option {{($object->type == 'other') ? 'selected' : ''}} value="other">other</option>
            </select>
        </div>
    </div>


    <div class="form-group">
        <div class="row">
            <div class="col-7">
                <label for="line1" class="col-form-label">Address</label>
                <input name="line1" class="form-control" type="text" placeholder="Line 1" value="{{$object->line1}}">
            </div>
            <div class="col-5 row">
                <label for="distance" class="col-form-label">Distance from Eagle</label>
                <div class="col-6">
                    <input name="distance" class="form-control" type="text" value="{{$object->distance}}" required>
                </div>
                <div class="col-6 p-0 pt-2 pl-1">
                    mile
                </div>
            </div>
        </div>

        <input name="line2" class="mt-1 form-control" type="text" placeholder="Line 2" value="{{$object->line2}}">

        <div class="row m-0 p-0">
            <input name="city" class="mt-1 col-6 form-control" type="text" placeholder="City" value="{{$object->city}}" required>
            <div class="col-2 m-0 py-0 px-1 mt-1">
                <input name="state" class=" form-control" type="text" placeholder="State" value="{{$object->state}}" required>
            </div>
            <input name="zip" class="mt-1 col-4 form-control" type="text" placeholder="Zip Code" value="{{$object->zip}}" required>
        </div>

    </div>
    <div class="form-row ">
        <div class="col-12 px-4">
            <input class="form-check-input" type="checkbox" id="updateGeo"  name="updateGeo" >
            <label for="updateGeo">Geo Update</label>
            <div class="">
                <small>Check it if the address was wrong and you already corrected it. This will update marker on map.</small>
            </div>
        </div>
    </div>

</div>
<div class="modal-footer">
    <input name="id" type="hidden" value="{{$object->id}}">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Save</button>
</div>
