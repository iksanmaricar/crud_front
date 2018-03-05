<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>JavaScript Shield UI Demos</title>
    <link id="themecss" rel="stylesheet" type="text/css" href="//www.shieldui.com/shared/components/latest/css/light/all.min.css" />
    <script type="text/javascript" src="//www.shieldui.com/shared/components/latest/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="//www.shieldui.com/shared/components/latest/js/shieldui-all.min.js"></script>
</head>
<body class="theme-light">
<div id="grid"></div>
<script type="text/javascript">
    $(function () {
        $("#grid").shieldGrid({
            dataSource: {
                events: {
                    error: function (event) {
                        if (event.errorType == "transport") {
                            // transport error is an ajax error; event holds the xhr object
                            alert("transport error: " + event.error.statusText);
                            // reload the data source if the operation that failed was save
                            if (event.operation == "save") {
                                this.read();
                            }
                        }
                        else {
                            // other data source error - validation, etc
                            alert(event.errorType + " error: " + event.error);
                        }
                    }
                },
                remote: {
                    read: {
                        type: "GET",
                        url: "http://localhost/api/medical",
                        dataType: "json",
                        operations: ["sort", "skip", "take","filter"],
                        data: function (params) {
                            var odataParams = {};
                            if (params.sort && params.sort.length) {
                                odataParams["$orderby"] = window.orderFields[params.sort[0].path].path + (params.sort[0].desc ? " desc" : "");
                            }
                            if (params.skip != null) {
                                odataParams["start"] = params.skip;
                            }
                            if (params.take != null) {
                                odataParams["limit"] = params.take;
                            }

                            if(params.filter != null){
                                var filter = params.filter.and;
                                var length_filter = filter.length;

                                for(i=0; i<length_filter; i++){
                                    //console.log(i);
                                    odataParams[filter[i].path] = filter[i].value;
                                }
                                console.log(params.filter);
                            }
                            
                            return odataParams;
                        }
                    },
                    modify: {
                        create: function (items, success, error) {
                            var newItem = items[0];
                            $.ajax({
                                type: "POST",
                                url: "http://localhost/api/medical",
                                dataType: "json",
                                contentType: "application/json",
                                data: JSON.stringify(newItem.data),
                                complete: function (xhr) {
                                    if (xhr.readyState == 4) {
                                        if (xhr.status == 200) {
                                            // update the id of the newly-created item with the 
                                            // one returned from the server in the Location hader url
                                            var location = xhr.getResponseHeader("Location");
                                            newItem.data.Id = +location.replace(/^.*?\/([\d]+)$/, "$1");
                                            success();
                                            return;
                                        }
                                    }
                                    error(xhr);
                                }
                            });
                        },
                        update: function (items, success, error) {
                            $.ajax({
                                type: "PUT",
                                url: "http://localhost/api/medical/" + items[0].data.api_medical_id,
                                dataType: "json",
                                contentType: "application/json",
                                data: JSON.stringify(items[0].data)
                            }).then(success, error);
                        },
                        remove: function (items, success, error) {
                            $.ajax({
                                type: "DELETE",
                                url: "http://localhost/api/medical/" + items[0].data.api_medical_id
                            }).then(success, error);
                        }
                    }
                },
                schema: {
                    fields: {
                        api_medical_id: { path: "api_medical_id", type: Number },
                        drg_definition: { path: "drg_definition", type: String },
                        provider_id: { path: "provider_id", type: String },
                        provider_name: { path: "provider_name", type: String },
                        provider_street_address: { path: "provider_street_address", type: String },
                        provider_city: { path: "provider_city", type: String },
                        provider_state: { path: "provider_state", type: String },
                        provider_zipcode: { path: "provider_zipcode", type: String },
                        average_covered_charge: { path: "average_covered_charge", type: Number },
                    },
                    data: "data",
                    total: function (result) {
                        return result["count"];
                    },
                }
            },
            sorting: true,
            rowHover: false,
            columns: [
                { field: "drg_definition", title: "Drg Definition", width: 120 },
                { field: "provider_id", title: "Provider ID", width: 120 },
                { field: "provider_name", title: "Provider Name", width: 200 },
                { field: "provider_street_address", title: "Provider Street Address", width: 200 },
                { field: "provider_city", title: "Provider City", width: 100 },
                { field: "provider_state", title: "Provider State", width: 50 },
                { field: "provider_zipcode", title: "Provider Zipcode", width: 50 },
                { field: "average_covered_charge", title: "Covered Charge", width: 50 },
                {
                    width: 140,
                    title: " ",
                    buttons: [
                        { commandName: "edit", caption: "Edit" },
                        { commandName: "delete", caption: "Delete" }
                    ]
                }
            ],
            toolbar: [
                {
                    buttons: [
                        { commandName: "insert", caption: "Add Medical" }
                    ],
                    position: "top"
                },
                {
                    buttons: [
                        {
                            caption: "Reset Medical",
                            click: function (e) {
                                var grid = this;
                                $.ajax({
                                    type: "PUT",
                                    url: "http://localhost/api/medical/"
                                }).done(function () {
                                    grid.dataSource.read();
                                });
                            }
                        }
                    ],
                    position: "bottom"
                }
            ],
            paging: {
                pageSize: 10
            },
            editing: {
                enabled: true,
                type: "row"
            },
            filtering: {
                enabled: true
            }
        });
    });
</script>
</body>
</html>