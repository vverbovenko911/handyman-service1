@if(isset($query->handyman))
<a href="{{ route('handyman.detail', ['id' => optional($query->handyman)->id]) }}">
  <div class="d-flex gap-3 align-items-center">
    <img src="{{ getSingleMedia(optional($query->handyman),'profile_image', null) }}" alt="avatar" class="avatar avatar-40 rounded-pill">
    <div class="text-start">
      <h6 class="m-0">{{ optional($query->handyman)->display_name }} </h6>
      <span>{{ optional($query->handyman)->email ?? '--' }}</span>
    </div>
  </div>
</a>
  @else

  <div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
</div>
@endif

