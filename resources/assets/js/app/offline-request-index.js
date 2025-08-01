$(function () {


  var dtTable = $('.datatables-offlineRequests');

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  if (dtTable.length) {
    var employeeView = baseUrl + 'employees/view/';

    var dtofflineRequests = dtTable.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'offlineRequests/indexAjax',
        /* data: function (d) {
          //d.dateFilter = date;
        }, */
        error: function (xhr, error, code) {
          console.log('Error: ' + error);
          console.log('Code: ' + code);
          console.log('Response: ' + xhr.responseText);
        }
      },
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // id
          targets: 1,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $id = full['id'];
            return '<span class="id">' + $id + '</span>';
          }
        },
        {
          // Name with avatar
          targets: 2,
          className: 'text-start',
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            var $name = full['user_name'],
              email = full['user_email'],
              initials = full['user_initial'],
              profileOutput,
              rowOutput;

            if (full['user_profile_image']) {
              profileOutput =
                '<img src="' + full['user_profile_image'] + '" alt="Avatar" class="avatar rounded-circle " />';
            } else {
              initials = full['user_initial'];
              profileOutput = '<span class="avatar-initial rounded-circle bg-label-info">' + initials + '</span>';
            }

            // Creates full output for row
            rowOutput =
              '<div class="d-flex justify-content-start align-items-center user-name">' +
              '<div class="avatar-wrapper">' +
              '<div class="avatar avatar-sm me-4">' +
              profileOutput +
              '</div>' +
              '</div>' +
              '<div class="d-flex flex-column">' +
              '<a href="' +
              employeeView +
              full['user_id'] +
              '" class="text-heading text-truncate"><span class="fw-medium">' +
              $name +
              '</span></a>' +
              '<small>' +
              email +
              '</small>' +
              '</div>' +
              '</div>';

            return rowOutput;
          }
        },
        {
          //  type
          targets: 3,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $type = full['type'];
            $type = $type.charAt(0).toUpperCase() + $type.slice(1);
            return '<span class="user-type">' + $type + '</span>';
          }
        },

        {
          // Plan
          targets: 4,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $plan = full['plan_id'];
            var $includedUsers = full['included_users'];
            var $duration = full['duration'];
            var $durationType = full['duration_type'];

            return (
              '<div class="d-flex flex-column">' +
              '<span class="user-code">' +
              $plan +
              '</span><br>' +
              '<strong>Duration:</strong><span class="user-code">' +
              $duration +
              ' ' +
              $durationType +
              '</span><br>' +
              '<strong>Included Users:</strong><span class="user-code">' +
              $includedUsers +
              '</span></div>'
            )
          }
        },
        {
          // Additional users
          targets: 5,
          className: 'text-start',
          render: function (data, type, full, meta) {
            return full['additional_users'];
          }
        },
        {
          // Total Amount
          targets: 6,
          className: 'text-start',
          render: function (data, type, full, meta) {
            return full['total_amount'];
          }
        },

        {
          // Order
          targets: 7,
          className: 'text-start',
          render: function (data, type, full, meta) {
            console.log(full);
            return full['order_id'] ?? 'N/A';
          }
        },

        {
          //status
          targets: 8,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $status = full['status'];
            if ($status == 'approved') {
              return '<span class="badge bg-label-success">Approved</span>';
            } else if ($status == 'rejected') {
              return '<span class="badge bg-label-danger">Rejected</span>';
            } else if ($status == 'cancelled') {
              return '<span class="badge bg-label-danger">Cancelled</span>';
            } else {
              return '<span class="badge bg-label-warning">Pending</span>';
            }
          }
        },
        {
          // Actions

          targets: 9,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {

            return (
              '<div class="d-flex align-items-left gap-50">' +
              `<button class="btn btn-sm btn-icon offline-request-details" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasOfflineRequestDetails"><i class="bx bx-show"></i></button>` +

              '</div>'
            );
          }
        }
      ],
      order: [[1, 'desc']],
      dom:
        '<"row"' +
        '<"col-md-2"<"ms-n2"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0"fB>>' +
        '>t' +
        '<"row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      lengthMenu: [7, 10, 20, 50, 70, 100], //for length of menu
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search Offline Requests',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries',
        paginate: {
          next: '<i class="bx bx-chevron-right bx-sm"></i>',
          previous: '<i class="bx bx-chevron-left bx-sm"></i>'
        }
      },
      // Buttons with Dropdown
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-label-secondary dropdown-toggle mx-4',
          text: '<i class="bx bx-export me-2 bx-sm"></i>Export',
          buttons: [
            {
              extend: 'print',
              title: 'Offline Requests',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be print
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              },
              customize: function (win) {
                //customize print view for dark
                $(win.document.body)
                  .css('color', config.colors.headingColor)
                  .css('border-color', config.colors.borderColor)
                  .css('background-color', config.colors.body);
                $(win.document.body)
                  .find('table')
                  .addClass('compact')
                  .css('color', 'inherit')
                  .css('border-color', 'inherit')
                  .css('background-color', 'inherit');
              }
            },
            {
              extend: 'csv',
              title: 'Offline Requests',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be print
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'excel',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'pdf',
              title: 'Offline Requests',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'copy',
              title: 'Offline Requests',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be copy
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            }
          ]
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();

              return 'Details of ' + data['name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIndex +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      }
    });
    // To remove default btn-secondary in export buttons
    $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
  }



   // leave request details
  $(document).on('click', '.offline-request-details', function () {
    var id = $(this).data('id');
    //get data
    $.get(`${baseUrl}offlineRequests/getByIdAjax/${id}`, function (response) {
      if (response.status === 'success') {
        var data = response.data;

        var statusQS = $('#status');
        var statusDiv = $('#statusDiv');
        var form = $('#offlineRequestForm');

        $('#id').val(data.id);
        $('#userName').text(data.userName);
        $('#userEmail').text(data.userEmail);
        $('#planName').text(data.planName);
        $('#additionalUsers').text(data.additionalUsers);
        $('#totalAmount').text(data.totalAmount);
        $('#createdAt').text(data.createdAt);
        $('#type').text(data.type);


            $('#admiNotes').val('');

            if (data.status === 'approved') {
                statusDiv.html('<span class="badge bg-label-success">Approved</span>');
            } else if (data.status === 'rejected') {
                statusDiv.html('<span class="badge bg-label-danger">Rejected</span>');
            } else if (data.status === 'cancelled') {
                statusDiv.html('<span class="badge bg-label-danger">Cancelled</span>');
            } else {

                statusDiv.html('<span class="badge bg-label-warning">Pending</span>');
                statusQS.empty();
                $('#statusDDDiv').show();
                statusQS.append(`<option value="approved">Approve</option>`);
                statusQS.append(`<option value="rejected">Reject</option>`);

                $('#actionButton').text('Submit');
                form.show();
            }

      }
    });
  });

});
