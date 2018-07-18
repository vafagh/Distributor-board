<div class="modal-body">
    <div class="form-group">
        <label class="col-form-label">ID : {{$object->id}}</label>
    </div>

    <div class="form-group">
        <label for="location" class="col-form-label">
            {{$object->rideable->type}} for {{$object->rideable->location->name}}
        </label>
        <p>
            Note: {{$object->rideable->description}}
        </p>
        <p>Status: <span class="text-success">{{$object->rideable->status}}</span></p>
    </div>

    <div class="form-group">
        <label for="driver" class="col-form-label">Driver:</label>
        <select class="form-control form-control-lg" name="driver">
            @foreach (App\Driver::all() as $driver)
                <option {{($object->driver->id == $driver->id) ? 'selected' : ''}} value="{{$driver->id}}">{{$driver->fname}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="truck" class="col-form-label">Truck:</label>
        <select class="form-control form-control-lg" name="truck">
            @foreach (App\Truck::all() as $truck)
                <option {{($object->truck->id == $truck->id) ? 'selected' : ''}} value="{{$truck->id}}">{{$truck->license_plate}}</option>
            @endforeach
        </select>
    </div>


</div>
<div class="modal-footer">
    <input name="id" type="hidden" value="{{$object->id}}">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Save</button>
</div>
