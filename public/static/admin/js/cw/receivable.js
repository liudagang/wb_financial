define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'cw.receivable/index',
        add_url: 'cw.receivable/add',
        edit_url: 'cw.receivable/edit',
        delete_url: 'cw.receivable/delete',
        newincome_url: 'cw.receivable/newincome',
        viewincome_url: 'cw.income/received',
        // export_url: 'cw.receivable/export',
        // modify_url: 'cw.receivable/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                toolbar: ['refresh','add'],
                cols: [[
                    // {type: 'checkbox'},
                    // {field: 'id', title: 'id', search: false},
                    {field: 'client_name', title: '客户名称', search:'select', selectList:g_opt['client']},
                    {field: 'deadline', title: '到期时间', search:'time', timeType:'date'},
                    {field: 'fee', title: '应收金额', search: false, totalRow: true},
                    {field: 'received_fee', title: '已收金额', search: false, totalRow: true},
                    {field: 'unreceive_fee', title: '未收金额', search: false, totalRow: true},
                    {field: 'type', title: '服务类型', search: false},
                    {field: 'service_time', title: '服务期间', search: false},
                    {field: 'project', title: '应收项目', selectList:g_opt['project']},
                    {field: 'remark', title: '备注', templet: ea.table.text, search:false, nosort:true},
                    {
                        width: 260,
                        title: '操作',
                        templet: ea.table.tool,
                        operat: [
                            'edit',
                            'delete',
                            [{
                                text: '添加收款',
                                url: init.newincome_url,
                                method: 'open',
                                auth: 'stock',
                                class: 'layui-btn layui-btn-xs layui-btn-normal',
                                // extend: 'data-full="true"',
                            },{
                                text: '查看收款',
                                url: init.viewincome_url,
                                method: 'open',
                                auth: 'stock',
                                class: 'layui-btn layui-btn-xs layui-btn-normal',
                            }]
                        ],
                        nosort: true
                    }
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

        add: function () {
            layui.laydate.render({ 
                elem: '#service_time'
                ,type: 'date'
                ,range: true //或 range: '~' 来自定义分割字符
            });

            layui.laydate.render({ 
                elem: '#deadline'
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
                elem: '#service_time'
                ,type: 'date'
                ,range: true //或 range: '~' 来自定义分割字符
            });

            layui.laydate.render({ 
                elem: '#deadline'
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

        byclient: function() {
            var init = {
                table_elem: '#currentTable',
                table_render_id: 'currentTableRenderId',
                index_url: 'cw.receivable/byclient',
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
                    {field: 'client_name', title: '客户名称', search:false, search: false},
                    {field: 'fee', title: '金额', search:false, totalRow: true},
                    {field: 'count', title: '笔数', search: false},
                    {field: 'deadline', title: '到期时间', search:'time', timeType:'date', hide: true},
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
                index_url: 'cw.receivable/byproject',
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
                    {field: 'deadline', title: '到期时间', search:'time', timeType:'date', hide: true},
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


        newincome: function () {
            layui.laydate.render({ 
                elem: '#income_date'
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