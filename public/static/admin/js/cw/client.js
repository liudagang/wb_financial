define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'cw.client/index',
        add_url: 'cw.client/add',
        edit_url: 'cw.client/edit',
        delete_url: 'cw.client/delete',
        // export_url: 'cw.client/export',
        // modify_url: 'cw.client/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                toolbar: ['refresh','add'],
                cols: [[
                    // {type: 'checkbox'},
                    {field: 'id', title: 'id', search: false},
                    {field: 'state', title: '状态'},
                    {field: 'code', title: '编号'},
                    {field: 'type', title: '类型'},
                    {field: 'tax_type', title: '税务类型'},
                    {field: 'name', title: '名称'},
                    {field: 'contact', title: '联系人'},
                    {field: 'tel', title: '联系电话'},
                    {field: 'addr', title: '地址'},
                    {field: 'src', title: '来源'},
                    {field: 'level', title: '等级'},
                    {field: 'remark', title: '备注', templet: ea.table.text, nosort:true},
                    {width: 250, title: '操作', templet: ea.table.tool},
                ]],
            });

            layui.table.on('sort(currentTableRenderId_LayFilter)', function(obj){
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
            ea.listen();
            jQuery('.layui-form-item .required').each(function(){
                var input = jQuery(this).parent().find('input[type=text]');
                input.attr('lay-verify', 'required');
                input.attr('autocomplete', 'off');
                input.attr('name', jQuery(this).attr('_name'));
            });
        },
        edit: function () {
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