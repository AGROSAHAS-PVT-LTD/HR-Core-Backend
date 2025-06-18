'use strict';

$(function () {
  var dt_table = $('.datatables-shifts');

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // team datatable
  if (dt_table.length) {
    var dt_shift = dt_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'shifts/getShiftsListAjax',
        error: function (xhr, error, code) {
          console.log('Error: ' + error);
          console.log('Code: ' + code);
          console.log('Response: ' + xhr.responseText);
        }
      },
      columns: [
        // columns according to JSON
        { data: '' },
        { data: 'id' },
        { data: 'name' },
        { data: 'code' },
        { data: 'shifts' },
        { data: 'status' },
        { data: 'action' }
      ],
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
          // name
          targets: 2,
          className: 'text-start',
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            var $name = full['name'];

            return '<span class="user-name">' + $name + '</span>';
          }
        },
        {
          // code
          targets: 3,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $code = full['code'];

            return '<span class="user-code">' + $code + '</span>';
          }
        },
        {
          // notes
          targets: 4,
          className: 'text-start',
          render: function (data, type, full, meta) {
            return `
        <div class="d-flex justify-content-start">
            <span class="badge ${full.sunday ? 'bg-label-success' : 'bg-label-secondary'} me-2">Sun</span>
            <span class="badge ${full.monday ? 'bg-label-success' : 'bg-label-secondary'} me-2">Mon</span>
            <span class="badge ${full.tuesday ? 'bg-label-success' : 'bg-label-secondary'} me-2">Tue</span>
            <span class="badge ${full.wednesday ? 'bg-label-success' : 'bg-label-secondary'} me-2">Wed</span>
            <span class="badge ${full.thursday ? 'bg-label-success' : 'bg-label-secondary'} me-2">Thu</span>
            <span class="badge ${full.friday ? 'bg-label-success' : 'bg-label-secondary'} me-2">Fri</span>
            <span class="badge ${full.saturday ? 'bg-label-success' : 'bg-label-secondary'} me-2">Sat</span>
        </div>
        `;
          }
        },

        {
          // status
          targets: 5,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $status = full['status'];

            var checked = $status === 'active' ? 'checked' : '';

            return `
                <div class= d-flex justify-content-left">
                <label class="switch mb-0">
                <input
                type="checkbox"
                class="switch-input status-toggle "
                id="statusToggle${full['id']}"
                data-id="${full['id']}"${checked} />
              <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              </label>
              </div>

            `;
          }
        },

        {
          // Actions
          targets: 6,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-flex align-items-left gap-50">' +
              `<button class="btn btn-sm btn-icon edit-record" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddOrUpdateShift"><i class="bx bx-edit"></i></button>` +
              `<button class="btn btn-sm btn-icon delete-record" data-id="${full['id']}"><i class="bx bx-trash"></i></button>` +
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
        searchPlaceholder: 'Search Shift',
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
              title: 'Shifts',
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
              title: 'Shift',
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
              title: 'Shift',
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
              title: 'Shift',
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

  var offCanvasForm = $('#offcanvasAddOrUpdateShift');

  $(document).on('click', '.add-new', function () {
    $('#id').val('');
    $('#notes').val('');
    $('#startTime').val('');
    $('#endTime').val('');
    $('#sundayToggle').prop('checked', false);
    $('#mondayToggle').prop('checked', true);
    $('#tuesdayToggle').prop('checked', true);
    $('#wednesdayToggle').prop('checked', true);
    $('#thursdayToggle').prop('checked', true);
    $('#fridayToggle').prop('checked', true);
    $('#saturdayToggle').prop('checked', true);
    $('#offcanvasShiftLabel').html('Add Shift');
    fv.resetForm(true);
    console.log('test');
    setCode();
  });

  function setCode() {
    $.get(`${baseUrl}shifts\/getCodeAjax`, function (data) {
      console.log(data);
      $('#code').val(data);
    });
  }

  $('#sundayToggle').on('change', function () {
    $('#sunday').val(this.checked ? 1 : 0);
    if (this.checked) {
      $('#sundayToggle').prop('checked', true);
      $('#sunday').val(0);
    }
  });

  $('#mondayToggle').on('change', function () {
    $('#monday').val(this.checked ? 1 : 0);
    if (this.checked) {
      $('#mondayToggle').prop('checked', true);
      $('#monday').val(1);
    }
  });

  $('#tuesdayToggle').on('change', function () {
    $('#tuesday').val(this.checked ? 1 : 0);
    if (this.checked) {
      $('#tuesdayToggle').prop('checked', true);
      $('#tuesday').val(1);
    }
  });

  $('#wednesdayToggle').on('change', function () {
    $('#wednesday').val(this.checked ? 1 : 0);
    if (this.checked) {
      $('#wednesdayToggle').prop('checked', true);
      $('#wednesday').val(1);
    }
  });

  $('#thursdayToggle').on('change', function () {
    $('#thursday').val(this.checked ? 1 : 0);
    if (this.checked) {
      $('#thursdayToggle').prop('checked', true);
      $('#thursday').val(1);
    }
  });

  $('#fridayToggle').on('change', function () {
    $('#friday').val(this.checked ? 1 : 0);
    if (this.checked) {
      $('#fridayToggle').prop('checked', true);
      $('#friday').val(1);
    }
  });

  $('#saturdayToggle').on('change', function () {
    $('#saturday').val(this.checked ? 1 : 0);
    if (this.checked) {
      $('#saturdayToggle').prop('checked', true);
      $('#saturday').val(1);
    }
  });

  const addShiftForm = document.getElementById('shiftForm');

  $(document).on('click', '.edit-record', function () {
    var id = $(this).data('id'),
      dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // changing the title of offcanvas
    $('#offcanvasShiftLabel').html('Edit Shift');

    // get data
    $.get(`${baseUrl}shifts\/getShiftAjax\/${id}`, function (data) {
      $('#id').val(data.id);
      $('#name').val(data.name);
      $('#code').val(data.code);
      $('#notes').val(data.notes);
      $('#startTime').val(data.startTime);
      $('#endTime').val(data.endTime);
      $('#notes').val(data.notes);
      $('#sundayToggle').prop('checked', data.sunday ? true : false);
      $('#mondayToggle').prop('checked', data.monday ? true : false);
      $('#tuesdayToggle').prop('checked', data.tuesday ? true : false);
      $('#wednesdayToggle').prop('checked', data.wednesday ? true : false);
      $('#thursdayToggle').prop('checked', data.thursday ? true : false);
      $('#fridayToggle').prop('checked', data.friday ? true : false);
      $('#saturdayToggle').prop('checked', data.saturday ? true : false);

    });
  });

  const fv = FormValidation.formValidation(addShiftForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'The name is required'
          }
        }
      },
      code: {
        validators: {
          notEmpty: {
            message: 'The code is required'
          },
          remote: {
            url: `${baseUrl}shifts/checkCodeValidationAjax`,
            message: 'The code is already taken',
            method: 'GET',
            data: function () {
              return {
                id: addShiftForm.querySelector('[name="id"]').value
              };
            }
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          return '.mb-6';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {
    // adding or updating user when form successfully validate
    $.ajax({
      data: $('#shiftForm').serialize(),
      url: `${baseUrl}shifts/addOrUpdateShiftAjax`,
      type: 'POST',
      success: function (response) {
        if (response.code === 200) {
          offCanvasForm.offcanvas('hide');
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${response.message}!`,
            text: `Shift ${response.message} Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });

          dt_shift.draw();
        }
      },
      error: function (err) {
        var responseJson = JSON.parse(err.responseText);
        console.log('Error Response: ' + JSON.stringify(responseJson));
        if (err.code === 400) {
          Swal.fire({
            title: 'Unable to create Shift',
            text: `${responseJson.data}`,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        } else {
          Swal.fire({
            title: 'Unable to create Shift',
            text: 'Please try again',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      }
    });
  });

  // clearing form data when offcanvas hidden
  offCanvasForm.on('hidden.bs.offcanvas', function () {
    fv.resetForm(true);
    $('#notes').val('');
    $('#id').val('');
  });

  $(document).on('click', '.delete-record', function () {
    var id = $(this).data('id');
    var dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        // delete the data
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}shifts/deleteShiftAjax/${id}`,
          success: function () {
            // success sweetalert
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'The shift has been deleted!',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });

            dt_shift.draw();
          },
          error: function (error) {
            console.log(error);
          }
        });
      }
    });
  });

  $(document).on('change', '.status-toggle', function () {
    var id = $(this).data('id');
    var status = $(this).is(':checked') ? 'Active' : 'Inactive';

    $.ajax({
      url: `${baseUrl}shifts/changeStatus/${id}`,
      type: 'POST',
      data: {
        status: status,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        console.log(response);

        dt_shift.draw();
      },
      error: function (response) {
        console.log(response);
      }
    });
  });
});

