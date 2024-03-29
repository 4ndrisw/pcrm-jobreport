// Init single jobreport
function init_jobreport(id) {
    load_small_table_item(id, '#jobreport', 'jobreportid', 'jobreports/get_jobreport_data_ajax', '.table-jobreports');
}


// Validates jobreport add/edit form
function validate_jobreport_form(selector) {

    selector = typeof (selector) == 'undefined' ? '#jobreport-form' : selector;

    appValidateForm($(selector), {
        clientid: {
            required: {
                depends: function () {
                    var customerRemoved = $('select#clientid').hasClass('customer-removed');
                    return !customerRemoved;
                }
            }
        },
        date: 'required',
        number: {
            required: true
        }
    });

    $("body").find('input[name="number"]').rules('add', {
        remote: {
            url: admin_url + "jobreports/validate_jobreport_number",
            type: 'post',
            data: {
                number: function () {
                    return $('input[name="number"]').val();
                },
                isedit: function () {
                    return $('input[name="number"]').data('isedit');
                },
                original_number: function () {
                    return $('input[name="number"]').data('original-number');
                },
                date: function () {
                    return $('body').find('.jobreport input[name="date"]').val();
                },
            }
        },
        messages: {
            remote: app.lang.jobreport_number_exists,
        }
    });

}


// Get the preview main values
function get_jobreport_item_preview_values() {
    var response = {};
    response.description = $('.main textarea[name="description"]').val();
    response.long_description = $('.main textarea[name="long_description"]').val();
    response.qty = $('.main input[name="quantity"]').val();
    return response;
}

// Append the added items to the preview to the table as items
function add_jobreport_item_to_table(data, itemid){

  // If not custom data passed get from the preview
  data = typeof (data) == 'undefined' || data == 'undefined' ? get_jobreport_item_preview_values() : data;
  if (data.description === "" && data.long_description === "") {
     return;
  }

  var table_row = '';
  var item_key = lastAddedItemKey ? lastAddedItemKey += 1 : $("body").find('tbody .item').length + 1;
  lastAddedItemKey = item_key;

  table_row += '<tr class="sortable item">';

  table_row += '<td class="dragger">';

  // Check if quantity is number
  if (isNaN(data.qty)) {
     data.qty = 1;
  }

  $("body").append('<div class="dt-loader"></div>');
  var regex = /<br[^>]*>/gi;

     table_row += '<input type="hidden" class="order" name="newitems[' + item_key + '][order]">';

     table_row += '</td>';

     table_row += '<td class="bold description"><textarea name="newitems[' + item_key + '][description]" class="form-control" rows="5">' + data.description + '</textarea></td>';

     table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description.replace(regex, "\n") + '</textarea></td>';
   //table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description + '</textarea></td>';


     table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="newitems[' + item_key + '][qty]" value="' + data.qty + '" class="form-control">';

     if (!data.unit || typeof (data.unit) == 'undefined') {
        data.unit = '';
     }

     table_row += '<input type="text" placeholder="' + app.lang.unit + '" name="newitems[' + item_key + '][unit]" class="form-control input-transparent text-right" value="' + data.unit + '">';

     table_row += '</td>';


     table_row += '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' + itemid + '); return false;"><i class="fa fa-trash"></i></a></td>';

     table_row += '</tr>';

     $('table.items tbody').append(table_row);

     $(document).trigger({
        type: "item-added-to-table",
        data: data,
        row: table_row
     });


     clear_item_preview_values();
     reorder_items();

     $('body').find('#items-warning').remove();
     $("body").find('.dt-loader').remove();

  return false;
}


// From jobreport table mark as
function jobreport_mark_as(status_id, jobreport_id) {
    var data = {};
    data.status = status_id;
    data.jobreportid = jobreport_id;
    //$.post(admin_url + 'jobreports/update_jobreport_status', data).done(function (response) {
    $.post(admin_url + 'jobreports/mark_action_status', status_id, jobreport_id).done(function (response) {
        //table_jobreports.DataTable().ajax.reload(null, false);
        reload_jobreports_tables();
    });
}


// From jobreport table mark as
function jobreport_remove_item(jobreport_id, task_id) {
    var data = {};
    data.jobreport_id = jobreport_id;
    data.task_id = task_id;
    $.post(admin_url + 'jobreports/remove_item', data).done(function (response) {
        reload_jobreports_tables();
    });
}

// From jobreport table mark as

function jobreport_add_item(jobreport_id, project_id, task_id) {
    var data = {};
    data.jobreport_id = jobreport_id;
    data.project_id = project_id;
    data.task_id = task_id;
    $.post(admin_url + 'jobreports/add_item', data).done(function (response) {
        reload_jobreports_tables();
    });
}


// Reload all jobreports possible table where the table data needs to be refreshed after an action is performed on task.
function reload_jobreports_tables() {
    var av_jobreports_tables = ['.table-jobreports', '.table-rel-jobreports', '.table-jobreport-items', '.table-jobreport-related'];
    $.each(av_jobreports_tables, function (i, selector) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload(null, false);
        }
    });
}
