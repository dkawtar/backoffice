$(function () {    /* Cliquer nom colonne */    $("table.table thead tr th").click(function (e) {        var href = $(this).find("a").attr("href");        if (href) {            location.href = href;        }    });    /* Suppression d'un client  */    // $(".btn_remove").click(function (e) {    //     var modal = $('#remove_modal');    //     var idObject = $.trim($(this).data("id"));    //     modal.find(".btn_remove_ok")    //         .data("id", idObject)    //         .data("title", $(this).data("title"));    //    //     modal.find(".modal-body").html("<h5>Vous voulez vraiment supprimer cet client  <b>(" + $(this).data("title") + ")</b>  ?</h5>");    //     modal.find(".modal-body h5").css("font-size", "16px");    //    //     modal.modal('show');    //     e.preventDefault();    // });    $('#modal_remove').on('show.bs.modal', function (event) {        var button = $(event.relatedTarget);        var modal = $(this);        modal.find(".btn_remove_ok")            .data("id", button.data('id'))            .data("title", button.data("title"));        modal.find(".modal-body .customer_name").html(button.data("title"));    });    $(".btn_remove_ok").click(function (e) {        e.preventDefault();        var customer = $(this).data("id");        var href = Routing.generate('back_customer_remove');        var data = {id: $.trim(customer.replace("customer_", ""))};        // console.log($(this).data("id"));        if (href != null && data != null) {            $.getJSON(href, data).done(function (data) {                // console.log(data);                if (data.result == "success") {                    $('#line_' + customer).fadeOut(500, function () {                        $(this).remove();                    });                    $.notify({                        icon: 'glyphicon glyphicon-ok',                        message: data.message,                    }, {                        type: "success",                        z_index: 9999,                        offset: 60,                        allow_dismiss: true,                        newest_on_top: false,                        showProgressbar: false,                        animate: {                            enter: 'animated fadeInDown',                            exit: 'animated fadeOutUp'                        },                    });                } else {                    $.notify({                        icon: 'glyphicon glyphicon-warning-sign',                        title: 'Error: ',                        message: 'Une erreur s\'est produit lors de la suppression de l\'utilisateur',                    }, {                        type: "danger",                        z_index: 9999,                        offset: 60,                        allow_dismiss: true,                        newest_on_top: false,                        showProgressbar: false,                        animate: {                            enter: 'animated fadeInDown',                            exit: 'animated fadeOutUp'                        },                    });                }            });        }    });    $('#modal_view').on('show.bs.modal', function (event) {        var button = $(event.relatedTarget);        var modal = $(this);        var customer = $("#line_" + button.data('id'));        modal.find(".name").html(customer.find(".customer_last_name").text() + " " + customer.find(".customer_first_name").text())        modal.find(".email").html(button.data('email'))        modal.find(".phone").html(button.data('phone'))        modal.find(".create").html(customer.find('.customer_created').text())        modal.find(".company_name").html(customer.find('.customer_company').data("name"))        modal.find(".representative").html(customer.find('.customer_company').data("representative"))        modal.find(".company_email").html(customer.find('.customer_company').data("email"))        modal.find(".company_siret").html(customer.find('.customer_company').data("siret"))        modal.find(".company_address").html(customer.find('.customer_company').data("address"))        modal.find(".company_country").html(customer.find('.customer_company').data("country"))        modal.find(".company_phone").html(customer.find('.customer_company').data("phone"))        modal.find(".company_mobile").html(customer.find('.customer_company').data("mobile"))        modal.find(".commercial_name").html(customer.find('.customer_company').data("name"))        modal.find(".commercial_email").html(customer.find('.customer_company').data("email"))        modal.find(".commercial_phone").html(customer.find('.customer_company').data("phone"))        modal.find(".customer_image").attr('src', customer.find('.customer_image').attr('src'))        modal.find("a.btn_edit").attr('href', customer.find('.link_edit').attr('href'))    });    // $('.btn_detail').click(function (e) {    //     e.defaultPrevented;    //     var modal = $('#remove_modal');    //    //     var idObject = $.trim($(this).data("id").replace("artist_", ""));    //    //     console.log(idObject);    //    //    // });});