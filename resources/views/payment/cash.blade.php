<x-master-layout>
<head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    </head>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
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
                  <form action="{{ route('payment.bulk-action') }}" id="quick-action-form" class="form-disabled d-flex gap-3 align-items-center">
                    @csrf
                  <select name="action_type" class="form-control select2" id="quick-action-type" style="width:100%" disabled>
                      <option value="">{{__('messages.no_action')}}</option>
                      <option value="change-status">{{__('messages.status')}}</option>
                      <option value="delete">{{__('messages.delete')}}</option>
                  </select>
                  <div class="select-status d-none quick-action-field" id="change-status-action" style="width:100%">
                    <select name="status" class="form-control select2" id="status" style="width:100%">
                      <option value="1">{{__('messages.approvecash')}}</option>
                    </select>
                </div>
                
                <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                data--submit="{{ route('payment.bulk-action') }}"
                data-datatable="reload" data-confirmation='true'
                data-title="{{ __('cash payment list',['form'=>  __('cash payment list') ]) }}"
                title="{{ __('cash payment list',['form'=>  __('cash payment list') ]) }}"
                data-message='{{ __("Do you want to perform this action?") }}' disabled>{{__('messages.apply')}}</button>
            </div>
          
            </form>
          </div>
              <div class="d-flex justify-content-end">
              <div class="datatable-filter ml-auto">
              <select class="select2 form-control" data-filter="select" id="statusSelect" style="width: 100%">
                    <option value="all" data-route="{{ route('payment.index')}}">{{__('messages.all')}}</option>
                    <option value="cash" data-route="{{ route('cash.list') }}" selected>{{__('messages.cash')}}</option>
                  
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
                  "url"    : '{{ route("cash.index_data")}}',
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
                        title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                        exportable: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'id',
                        name: 'id',
                        title: "{{__('messages.id')}}"
                    },
                    {
                        data: 'booking_id',
                        name: 'booking_id',
                        title: "{{__('messages.service')}}"
                    },
                    {
                        data: 'customer_id',
                        name: 'customer_id',
                        title: "{{__('messages.user')}}",
                         orderable: false,
                    },
                    {
                        data: 'datetime',
                        name: 'datetime',
                        title: "{{__('messages.datetime')}}"
                    },
                    {
                        data: 'history',
                        name: 'history',
                        title: "{{__('messages.history')}}",
                        orderable: false,
                        searchable:false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        title: "{{__('messages.status')}}"
                    },
                   
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        title: "{{__('messages.price')}}"
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: "{{__('messages.action')}}"
                    }
                    
                ]
                
            });
      });

      $(document).ready(function() {
        $('#statusSelect').change(function() {
            var selectedValue = $(this).val();
            var selectedOption = $('#statusSelect option:selected');
            var route = selectedOption.data('route');

            if (selectedValue === 'cash' && route) {
                window.location.href = route;
            }
            window.location.href = route;
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
    } else {
        $('#quick-action-apply').attr('disabled', true);
        $('.quick-action-field').addClass('d-none');
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