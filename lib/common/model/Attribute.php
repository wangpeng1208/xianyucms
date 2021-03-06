<?php

namespace app\common\model;
class Attribute extends Model
{
    protected $type = array(
        'id' => 'integer',
    );

    protected function getTypeTextAttr($value, $data)
    {
        $type = config('config_type_list');
        $type_text = explode(',', $type[$data['type']]);
        return $type_text[0];
    }

    public function getFieldlist($map, $index = 'id')
    {
        $list = array();
        $row = db('Attribute')->field('*,remark as help,type,extra as "option"')->where($map)->select();
        foreach ($row as $key => $value) {
            if (in_array($value['type'], array('checkbox', 'radio', 'select', 'bool'))) {
                $value['option'] = parse_field_attr($value['extra']);
            } elseif ($value['type'] == 'bind') {
                $extra = parse_field_bind($value['extra']);
                foreach ($extra as $k => $v) {
                    $option[$v['id']] = $v['title_show'];
                }
                $value['option'] = $option;
            }
            $list[$value['id']] = $value;
        }
        return $list;
    }

    public function change()
    {
        $data = \think\Request::instance()->post();

        if ($data['id']) {
            $status = $this->validate('attribute.edit')->save($data, array('id' => $data['id']));
        } else {
            $status = $this->validate('attribute.add')->save($data);
        }

        if (false !== $status) {
            //在数据库内添加字段
            $result = $this->checkTableField($data);
            if (!$result) {
                $this->error = "字段创建失败！";
                return false;
            }
            return $status;
        } else {
            return false;
        }
    }

    public function del($id)
    {
        $map['id'] = $id;
        $info = $this->find($id);
        $model = db('Model')->where(array('id' => $info['model_id']))->find();

        //先删除字段表内的数据
        $result = $this->where($map)->delete();
        if ($result) {
            if ($model['extend'] == 1) {
                $tablename = 'document_' . $model['name'];
            } else {
                $tablename = $model['name'];
            }

            //删除模型表中字段
            $db = new \com\Datatable();
            $result = $db->del_field($tablename, $info['name'])->query();
            if ($result) {
                return true;
            } else {
                $this->error = "删除失败！";
                return false;
            }
        } else {
            $this->error = "删除失败！";
            return false;
        }
    }

    protected function checkTableField($field)
    {
        $model = db('Model')->find($field['model_id']);
        if ($model['extend'] == 1) {
            $tablename = 'document_' . $model['name'];
            $key = "doc_id";
        } else {
            $tablename = $model['name'];
            $key = "id";
        }

        //实例化一个数据库操作类
        $db = new \com\Datatable();
        //检查表是否存在并创建
        if (!$db->CheckTable($tablename)) {
            //创建新表
            $db->start_table($tablename)->create_id($key)->create_key($key)->end_table()->query();
        };
        $oldname = "";
        if ($field['id']) {
            $oldname = $this->db()->where(array('id' => $field['id']))->value('name');
        }
        $attribute_type = get_attribute_type();
        $field['field'] = $field['name'];
        $field['type'] = $attribute_type[$field['type']][1];
        $field['is_null'] = $field['is_must'];  //是否为null
        $field['default'] = $field['value'];    //字段默认值
        $field['comment'] = $field['remark'];   //字段注释
        if ($db->CheckField($tablename, $oldname) && $oldname) {
            $field['action'] = 'CHANGE';
            $field['oldname'] = $oldname;
            $field['newname'] = $field['name'];
            $db->colum_field($tablename, $field);
        } else {
            $field['action'] = 'ADD';
            $db->colum_field($tablename, $field);
        }

        $result = $db->create();
        return $result;
    }

    function get_attribute_type($type = '')
    {
        // TODO 可以加入系统配置
        $type_array = config('config_type_list');
        static $type_list = array();
        foreach ($type_array as $key => $value) {
            $type_list[$key] = explode(',', $value);
        }
        return $type ? $type_list[$type][0] : $type_list;
    }
}