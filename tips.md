Yii2 tips
=======

### 在view文件中createUrl 和Html链接
```
<?php echo \Yii::$app->getUrlManager()->createUrl(['catalog/create', 'parent_id'=>$item['id']]); ?
Html::a(Html::encode($tag), ['blog/index','tag'=>$tag]);
```