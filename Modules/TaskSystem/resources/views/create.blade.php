@php
  $title = 'Create Task';
@endphp

@extends('layouts/layoutMaster')

@section('content')
  <div class="row mb-3">
    <div class="col">
      <div class="float-start">
        <h4 class="mt-2">{{ $title }}</h4>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <form method="POST" action="{{ route('task.store') }}" autocomplete="off">
            @csrf
            <input type="hidden" id="Latitude" name="Latitude" />
            <input type="hidden" id="Longitude" name="Longitude" />
            <input type="hidden" id="MaxRadius" name="MaxRadius" value="100" />

            <div class="form-floating mb-4">
              <select class="form-select" id="TaskType" name="TaskType" required>
                <option value="">Select Task Type</option>
                <option value="1">Client Based</option>
                <option value="2">Open</option>
              </select>
              <label for="TaskType">Task Type</label>
            </div>

            <div class="form-floating mb-4" id="clientDiv" style="display:none;">
              <select class="form-select" id="ClientId" name="ClientId">
                <option value="">Select a Client</option>
                @foreach($clients as $client)
                  <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
              </select>
              <label for="ClientId">Client</label>
            </div>

            <div class="form-floating mb-4">
              <input class="form-control" id="Title" name="Title" type="text" placeholder="Task Title" required />
              <label for="Title">Title</label>
            </div>

            <div class="form-floating mb-4">
                <textarea class="form-control" id="Description" name="Description"
                          placeholder="Task Description"></textarea>
              <label for="Description">Description</label>
            </div>

            <div class="form-floating mb-4">
              <select class="form-select" id="EmployeeId" name="EmployeeId">
                <option value="">Assign to Employee</option>
                @foreach($employees as $employee)
                  <option
                    value="{{ $employee->id }}">{{ $employee->first_name . ' ' . $employee->last_name }}</option>
                @endforeach
              </select>
              <label for="EmployeeId">Employee</label>
            </div>

            <div class="form-floating mb-4">
              <input class="form-control" id="ForDate" name="ForDate" type="datetime-local" required />
              <label for="ForDate">For Date</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Task</button>
          </form>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            <label for="pac-input" class="form-label">Search Location</label>
            <input id="pac-input" class="form-control" type="text" placeholder="Search location here" />
          </div>
          <div id="map" style="height: 400px; width: 100%;"></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initMap"
    async defer></script>

  <script>
    let map, radius = 100;
    let markers = [];
    let circles = [];

    function initMap() {
      const defaultLocation = {
        lat: parseFloat('{{ $settings->center_latitude }}'),
        lng: parseFloat('{{ $settings->center_longitude }}')
      };

      map = new google.maps.Map(document.getElementById('map'), {
        center: defaultLocation,
        zoom: parseInt('{{ $settings->map_zoom_level }}'),
        mapTypeId: 'roadmap'
      });

      const searchBox = new google.maps.places.SearchBox(document.getElementById('pac-input'));

      searchBox.addListener('places_changed', () => {
        const places = searchBox.getPlaces();
        if (places.length === 0) return;

        clearMarkers();

        const bounds = new google.maps.LatLngBounds();
        places.forEach((place) => {
          if (!place.geometry || !place.geometry.location) return;

          const marker = new google.maps.Marker({
            map,
            position: place.geometry.location
          });
          markers.push(marker);

          const circle = new google.maps.Circle({
            map,
            center: place.geometry.location,
            radius,
            fillColor: '#0000FF',
            fillOpacity: 0.2,
            strokeWeight: 1
          });
          circles.push(circle);

          document.getElementById('Latitude').value = place.geometry.location.lat();
          document.getElementById('Longitude').value = place.geometry.location.lng();

          bounds.extend(place.geometry.location);
        });

        map.fitBounds(bounds);
      });

      map.addListener('click', (event) => {
        clearMarkers();

        const marker = new google.maps.Marker({
          position: event.latLng,
          map
        });
        markers.push(marker);

        const circle = new google.maps.Circle({
          map,
          center: event.latLng,
          radius,
          fillColor: '#0000FF',
          fillOpacity: 0.2,
          strokeWeight: 1
        });
        circles.push(circle);

        document.getElementById('Latitude').value = event.latLng.lat();
        document.getElementById('Longitude').value = event.latLng.lng();
      });
    }

    function clearMarkers() {
      markers.forEach(marker => marker.setMap(null));
      circles.forEach(circle => circle.setMap(null));
      markers = [];
      circles = [];
    }

    $('#TaskType').on('change', function() {
      $('#clientDiv').toggle(this.value === '1');
    });

    $('#ClientId').on('change', function() {
      if (!this.value) return;

      $.post('{{ route('task.getClientLocation') }}', {
        clientId: this.value,
        _token: '{{ csrf_token() }}'
      }, function(data) {
        if (data) {
          map.setCenter({ lat: parseFloat(data.latitude), lng: parseFloat(data.longitude) });
          map.setZoom(15);
          document.getElementById('Latitude').value = data.latitude;
          document.getElementById('Longitude').value = data.longitude;

          clearMarkers();

          const marker = new google.maps.Marker({
            position: { lat: parseFloat(data.latitude), lng: parseFloat(data.longitude) },
            map
          });
          markers.push(marker);

          const circle = new google.maps.Circle({
            map,
            center: marker.getPosition(),
            radius,
            fillColor: '#0000FF',
            fillOpacity: 0.2,
            strokeWeight: 1
          });
          circles.push(circle);
        }
      });
    });
  </script>
@endsection
