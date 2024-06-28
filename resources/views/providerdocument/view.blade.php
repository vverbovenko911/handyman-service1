<x-master-layout>
<head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    </head>
    <div class="container-fluid">
    @include('partials._provider')
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                            @if($auth_user->can('providerdocument add'))
                            <a href="{{ route('providerdocument.create',['providerdocument' => $providerdata->id]) }}" class="float-right mr-1 btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('messages.add_form_title',['form' => trans('messages.providerdocument')  ]) }}</a>
                            @endif
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="card">
        <div class="card-body">
        <div class="row justify-content-between">
            <div>
                <div class="col-md-12">
                  <form action="{{ route('providerdocument.bulk-action') }}" id="quick-action-form" class="form-disabled d-flex gap-3 align-items-center">
                    @csrf
                  <select name="action_type" class="form-control select2" id="quick-action-type" style="width:100%" disabled>
                      <option value="">{{ __('messages.no_action') }}</option>
                      <option value="change-featured">{{ __('messages.verified') }}</option>
                      <option value="delete">{{ __('messages.delete') }}</option>
                      <option value="restore">{{ __('messages.restore') }}</option>
                      <option value="permanently-delete">{{ __('messages.permanent_dlt') }}</option>
                  </select>
                  <div class="select-status d-none quick-action-featured" id="change-featured-action" style="width:100%">
                    <select name="is_verified" class="form-control select2" id="is_verified" >
                      <option value="1">{{ __('messages.verified') }}</option>
                      <option value="0">{{ __('messages.unverified') }}</option>
                    </select>
                </div>
                
                <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                data--submit="{{ route('providerdocument.bulk-action') }}"
                data-datatable="reload" data-confirmation='true'
                data-title="{{ __('providerdocument',['form'=>  __('providerdocument') ]) }}"
                title="{{ __('providerdocument',['form'=>  __('providerdocument') ]) }}"
                data-message='{{ __("Do you want to perform this action?") }}' disabled>{{ __('messages.apply') }}</button>
                </form>
            </div>
          
            
          </div>
              <div class="d-flex justify-content-end">
              <div class="datatable-filter ml-auto">
                  <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                    <option value="">{{__('messages.all')}}</option>
                    <option value="0" {{$filter['is_verified'] == '0' ? "selected" : ''}}>{{__('messages.unverified')}}</option>
                    <option value="1" {{$filter['is_verified'] == '1' ? "selected" : ''}}>{{__('messages.verified')}}</option>
                  </select>
                </div>
                <div class="input-group ml-2">
                    <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="Search..." aria-label="Search" aria-describedby="addon-wrapping" aria-controls="dataTableBuilder">
                  </div>
              </div>
               
              <div class="table-responsive">
                <table id="datatable" class="table table-striped border">

                </table>
              </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {

        window.renderedDataTable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6" p>><"clear">',
                ajax: {
                  "type"   : "GET",
                  "url": '{{ route("providerdocument.index_data", ["providerdocument" => $providerdata->id]) }}',
                  "data"   : function( d ) {
                    d.search = {
                      value: $('.dt-search').val()
                    };
                    d.filter = {
                      column_status: $('#column_status').val()
                    }
                  },
                },
                columns: [
                    {
                        name: 'check',
                        data: 'check',
                        title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="providerdocument" onclick="selectAllTable(this)">',
                        exportable: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'provider_id',
                        name: 'provider_id',
                        title: "{{ __('messages.provider') }}",
                         orderable: false,
                    },
                    {
                        data: 'document_id',
                        name: 'document_id',
                        title: "{{ __('messages.document') }}"
                    },
                    {
                        data: 'is_verified',
                        name: 'is_verified',
                        title: "{{ __('messages.is_verified') }}"
                    },
                    
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: "{{ __('messages.action') }}"
                    }
                    
                ]
                
            });
      });

    function resetQuickAction () {
    const actionValue = $('#quick-action-type').val();
    console.log(actionValue)
    if (actionValue != '') {
        $('#quick-action-apply').removeAttr('disabled');

        if (actionValue == 'change-status') {
            $('.quick-action-field').addClass('d-none');
            $('#change-status-action').removeClass('d-none');
        } else {
            $('.quick-action-field').addClass('d-none');
        }
        if (actionValue == 'change-featured') {
                $('.quick-action-featured').addClass('d-none');
                $('#change-featured-action').removeClass('d-none');
        } else {
                $('.quick-action-featured').addClass('d-none');
        }
        } else {
            $('#quick-action-apply').attr('disabled', true);
            $('.quick-action-field').addClass('d-none');
            $('.quick-action-featured').addClass('d-none');
        }
  }

  $('#quick-action-type').change(function () {
    resetQuickAction()
  });

  $(document).on('update_quick_action', function() {

  })

    $(document).on('click', '[data-ajax="true"]', function (e) {
      e.preventDefault();
      const button = $(this);
      const confirmation = button.data('confirmation');

      if (confirmation === 'true') {
          const message = button.data('message');
          if (confirm(message)) {
              const submitUrl = button.data('submit');
              const form = button.closest('form');
              form.attr('action', submitUrl);
              form.submit();
          }
      } else {
          const submitUrl = button.data('submit');
          const form = button.closest('form');
          form.attr('action', submitUrl);
          form.submit();
      }
  });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</x-master-layout>