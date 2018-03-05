<?php

/**
 * This is the model class for table "tbl_post".
 *
 * The followings are the available columns in table 'tbl_post':
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $tags
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $author_id
 *
 * The followings are the available model relations:
 * @property TblUser $author
 * @property TblComment[] $tblComments
 */
class Post extends CActiveRecord
{
    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_ARCHIVED = 3;

    private $_oldTags;

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Post the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }


    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'tbl_post';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('title, content, status', 'required'),
            array('title', 'length', 'max'=>128),
            array('status', 'in', 'range'=>array(1,2,3)),
            array('tags', 'match', 'pattern'=>'/^[\w\s,]$/',
                'message'=>'Tags can only contain word characters.'),
            array('title, status', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     * relations() 中声明的关系表明：
     * 一篇日志属于一个作者，它的类是 User ，它们的关系建立在日志的 author_id 属性值之上；
     * 一篇日志有多个评论，它们的类是 Comment ，它们的关系建立在评论的 post_id 属性值之上。
     *** 这些评论应该按它们的创建时间排列，且评论必须已通过审核；
     * commentCount 关系有一点特别，它返回一个关于日志有多少条评论的一个聚合结果。
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'author' => array(self::BELONGS_TO, 'User', 'author_id'),
            'comments' => array(self::HAS_MANY, 'Comment', 'post_id',
                'condition' => 'comments.status='.Comment::STATUS_APPROVED,
                'order' => 'comments.create_time DESC'),
            'commentCount' => array(self::STAT, 'Comment', 'post_id',
                'condition' => 'status='.Comment::STATUS_APPROVED)
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'tags' => 'Tags',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'author_id' => 'Author',
        );
    }


    /**
     * 日志是一份可以通过一个唯一的URL访问的内容。
     * @return string the URL that shows the detail of the post
     */
    public function getUrl()
    {
        return Yii::app()->createUrl('post/view', array(
            'id'=>$this->id,
            'title'=>$this->title,
        ));
    }

    /**
     * @return array a list of links that point to the post list filtered by every tag of the post
     */
    public function getTagLinks()
    {
        $links=array();
        foreach(Tag::string2array($this->tags) as $tag)
            $links[]=CHtml::link(CHtml::encode($tag), array('post/index', 'tag'=>$tag));
        return $links;
    }


    /**
     * Normalizes the user-entered tags.
     */
	public function normalizeTags($attribute, $params)
    {
        $this->tags=Tag::array2string(array_unique(Tag::string2array($this->tags)));
    }

    /**
     * Adds a new comment to this post.
     * This method will set status and post_id of the comment accordingly
     * @param Comment the comment to be added
     * @return boolean wheter the comment is saved successfully
     */
    public function addComment($comment)
    {
        if(Yii::app()->params['commentNeedApproval'])
            $comment->status=Comment::STATUS_PENDING;
        else
            $comment->status=Comment::STATUS_APPROVED;
        $comment->post_id=$this->id;
        return $comment->save();
    }


    /**
     * @override afterFind()
     * This is invoked when a record is populated with data from a find() call.
     */
    protected function afterFind()
    {
        parent::afterFind();
        $this->_oldTags=$this->tags;
    }
    

    /**
     * @override beforeSave()
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    protected function beforeSave()
    {
        if(parent::beforeSave())
        {
            if($this->isNewRecord)
            {
                $this->create_time=$this->update_time=time();
                $this->author_id=Yii::app()->user->id;
            }
            else
                $this->update_time=time();
            return true;
        }
        return false;
    }

    /**
     * @override afterSave()
     * This is invoked after the record is saved.
     */

    protected function afterSave()
    {
        parent::afterSave();
        Tag::model()->updateFrequency($this->_oldTags, $this->tags);
    }

    /**
     * @override afterDelete()
     * This is invoked after the record is deleted.
     */
    protected function afterDelete()
    {
        parent::afterDelete();
        Comment::model()->deleteAll('post_id='.$this->id);
        Tag::model()->updateFrequency($this->tags, '');
    }

    /**
     * Retrieves the list of posts based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the needed posts.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('title',$this->title,true);
        $criteria->compare('status',$this->status);

        return new CActiveDataProvider('Post', array(
            'criteria'=>$criteria,
            'sort'=>array(
                'defaultOrder'=>'status, update_time DESC',
            ),
        ));
    }

}
