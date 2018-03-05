<?php
/* @var $this PostController */
/* @var $model Post */

$this->breadcrumbs=array(
	'Create Post',
);
?>

<h1>Create Post</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>