$(document).ready(function () {
    $("#showGoods").on('click', function (e) {
        e.preventDefault();

        let priceType = $("#priceType").val();
        let minPrice = $("#minPrice").val().trim();
        let maxPrice = $("#maxPrice").val().trim();
        let moreLess = $("#moreLess").val();
        let quantity = $("#quantity").val().trim();

        if(minPrice == "") {
            $("#errorMessage").text("Введите минимальную цену");
            return false;
        } else if(maxPrice == "") {
            $("#errorMessage").text("Введите максимальную цену");
            return false;
        } else if(quantity == "") {
            $("#errorMessage").text("Введите количество товаров на складе");
            return false;
        }

        if(!$.isNumeric(minPrice) || minPrice < 0) {
            $("#errorMessage").text("Введите корректную минимальную цену");
            return false;
        } else if(!$.isNumeric(maxPrice) || maxPrice < 0) {
            $("#errorMessage").text("Введите корректную максимальную цену");
            return false;
        } else if(!$.isNumeric(quantity) || quantity < 0) {
            $("#errorMessage").text("Введите корректное количество товаров на складе");
            return false;
        }

        $("#errorMessage").text("");

        $.ajax({
            url: 'filter.php',
            type: 'POST',
            cache: false,
            data: { 'priceType': priceType, 'minPrice': minPrice, 'maxPrice': maxPrice, 'moreLess': moreLess, 'quantity': quantity },
            dataType: 'html',
            beforeSend: function () {
                $("#showGoods").prop("disabled", true);
            },
            success: function (data) {
                $(".content .container").html(data);
                $("#showGoods").prop("disabled", false);
            }
        });
    })
})