define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'cw.payment/index',
        add_url: 'cw.payment/add',
        edit_url: 'cw.payment/edit',
        delete_url: 'cw.payment/delete',
        // export_url: 'cw.payment/export',
        // modify_url: 'cw.payment/modify',
    };


    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                toolbar: ['refresh','add'],
                cols: [[
                    {field: 'id', title: 'id', search: false},
                    {field: 'fee', title: '金额', search:false, totalRow: true},
                    {field: 'type', title: '支出类型', search:false},
                    {field: 'supplier_name', title: '供应商名称', search:'select', selectList:g_opt['supplier']},
                    {field: 'account', title: '支出账户', search:'select', selectList:g_opt['account']},
                    {field: 'pay_date', title: '支出日期', search:'range', timeType:'date'},
                    {field: 'project', title: '支出项目', search:'select', selectList:g_opt['project']},
                    {field: 'remark', title: '备注', templet: ea.table.text, search:false, nosort:true},
                    {width: 250, title: '操作', templet: ea.table.tool, nosort:true},
                ]],
                totalRow: true //开启合计行
            });
            
            // lay-filter = init.table_render_id + '_LayFilter'
            layui.table.on('sort(currentTableRenderId_LayFilter)', function(obj){
                console.log(obj.field); //当前排序的字段名
                console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
                console.log(obj); //当前排序的 th 对象

                layui.table.reload('currentTableRenderId', {
                    // initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。
                    where : {
                        field: obj.field, //排序字段
                        order: obj.type //排序方式
                    }
                  }, 'data');
            });

            ea.listen();
        },

        bysupplier: function() {
            var init = {
                table_elem: '#currentTable',
                table_render_id: 'currentTableRenderId',
                index_url: 'cw.payment/bysupplier',
            };

            ea.table.render({
                init: init,
                toolbar: ['refresh'],
                cols: [[
                    // {type: 'checkbox'},
                    {field: 'supplier_name', title: '供应商', search:false, search: false},
                    {field: 'fee', title: '金额', search:false, totalRow: true},
                    {field: 'count', title: '笔数', search: false},
                    {field: 'pay_date', title: '支出日期', search:'range', timeType:'date', hide: true},
                ]],
                totalRow: true //开启合计行
            });

            layui.table.on('sort(currentTableRenderId_LayFilter)', function(obj){
                console.log(obj.field); //当前排序的字段名
                console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
                console.log(obj); //当前排序的 th 对象

                layui.table.reload('currentTableRenderId', {
                    // initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。
                    where : {
                        field: obj.field, //排序字段
                        order: obj.type //排序方式
                    }
                  }, 'data');
            });

            ea.listen();
        },


        byproject: function() {
            var init = {
                table_elem: '#currentTable',
                table_render_id: 'currentTableRenderId',
                index_url: 'cw.payment/byproject',
                // add_url: 'cw.income/add',
                // edit_url: 'cw.income/edit',
                // delete_url: 'cw.income/delete',
                // export_url: 'cw.income/export',
                // modify_url: 'cw.income/modify',
            };

            ea.table.render({
                init: init,
                toolbar: ['refresh'],
                cols: [[
                    // {type: 'checkbox'},
                    {field: 'project', title: '项目名称', search:false, search: false},
                    {field: 'fee', title: '金额', search:false, totalRow: true},
                    {field: 'count', title: '笔数', search: false},
                    {field: 'pay_date', title: '支出日期', search:'range', timeType:'date', hide: true},
                ]],
                totalRow: true //开启合计行
            });

            layui.table.on('sort(currentTableRenderId_LayFilter)', function(obj){
                console.log(obj.field); //当前排序的字段名
                console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
                console.log(obj); //当前排序的 th 对象

                layui.table.reload('currentTableRenderId', {
                    // initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。
                    where : {
                        field: obj.field, //排序字段
                        order: obj.type //排序方式
                    }
                  }, 'data');
            });

            ea.listen();
        },


        add: function () {
            layui.laydate.render({ 
                elem: '#pay_date'
                ,type: 'date'
            });

            ea.listen();

            jQuery('.layui-form-item .required').each(function(){
                var input = jQuery(this).parent().find('input[type=text]');
                input.attr('lay-verify', 'required');
                input.attr('autocomplete', 'off');
                input.attr('name', jQuery(this).attr('_name'));
            });
        },

        
        edit: function () {
            layui.laydate.render({ 
                elem: '#pay_date'
                ,type: 'date'
            });

            ea.listen();

            jQuery('.layui-form-item .required').each(function(){
                var input = jQuery(this).parent().find('input[type=text]');
                input.attr('lay-verify', 'required');
                input.attr('autocomplete', 'off');
                input.attr('name', jQuery(this).attr('_name'));
            });
        },
    };
    return Controller;
});