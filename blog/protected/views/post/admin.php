<?php
$this->breadcrumbs=array(
        'Manage Posts',
);
?>

<h1>Manage Posts</h1>

<?php
/**
 * 我们使用 CGridView 来显示这些日志。
 * 它允许我们在单页显示过多时可以分页并可以按某一列排序。
 * 我们的修改主要针对每一列的显示。例如，针对 title 列，
 * 我们指定它应该显示为一个超级链接，指向日志的详情页面。
 * 表达式$data->url 返回我们之前在 Post 类中定义的 url 属性值。
 */
?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider'=>$model->search(),
        'filter'=>$model,
        'columns'=>array(
                array(
                        'name'=>'title',
                        'type'=>'raw',
                        'value'=>'CHtml::link(CHtml::encode($data->title), $data->url)'
                ),
                array(
                        'name'=>'status',
                        'value'=>'Lookup::item("PostStatus", $data->status)',
                        'filter'=>Lookup::items('PostStatus'),
                ),
                array(
                        'name'=>'create_time',
                        'type'=>'datetime',
                        'filter'=>false,
                ),
                array(
                        'class'=>'CButtonColumn',
                ),
        ),
)); ?>
