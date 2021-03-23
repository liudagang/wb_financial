define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'cw.income/index',
        add_url: 'cw.income/add',
        edit_url: 'cw.income/edit',
        delete_url: 'cw.income/delete',
        // export_url: 'cw.income/export',
        // modify_url: 'cw.income/modify',
    };

    var Controller = {

        index: function () {
            // get search options


            var tb = ea.table.render({
                init: init,
                toolbar: ['refresh','add'],
                cols: [[
                    // {type: 'checkbox'},
                    {field: 'id', title: 'id', search: false},
                    {field: 'fee', title: '金额', search:false, totalRow: true},
                    {field: 'service_time', title: '服务期间', search: false},
                    // {field: 'service_end', title: '服务期间-结束'},
                    {field: 'type', title: '收入类型', search:false},
                    {field: 'client_name', title: '客户名称', search:'select', selectList:g_opt['client']},
                    {field: 'account', title: '收入账户', search:'select', selectList:g_opt['account']},
                    {field: 'income_date', title: '收款日期', search:'range', timeType:'date'},
                    {field: 'project', title: '收入项目', search:'select', selectList:g_opt['project']},
                    {field: 'remark', title: '备注', templet: ea.table.text, search:false, nosort:true},
                    // {field: 'handler', title: 'handler'},
                    // {field: 'from_receivable', title: 'from_receivable'},
                    // {field: 'add_time', title: 'add_time'},
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


        add: function () {
            layui.laydate.render({ 
                elem: '#service_time'
                ,type: 'date'
                ,range: true //或 range: '~' 来自定义分割字符
            });

            // console.log(window);
            // jQuery('#type_select')[0].removeEventListener('blur', window.getEventListeners(jQuery('#type_select')[0]).blur[0].listener)

            ea.listen();
        },

        
        edit: function () {
            ea.listen();
        },
    };
    return Controller;
});