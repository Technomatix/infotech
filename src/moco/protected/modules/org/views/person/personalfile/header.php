<?php
use usr\models\entities\User;

/**
 * @var \org\models\entities\Person $person
 */
?>

<div id="header-block" class="col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding title">
        <div class="pull-left">
            <h3><?= $title ?></h3>
        </div>
        <?php
                $now = time();
                $dismiss = '';
                if (!$person->fireDate instanceof \DateTime) {
                    $dismiss = 'style="display: none;"';
                }
            ?>
            <div class="status pull-left" id="personDismiss" <?php echo $dismiss; ?>>
                <?php echo TbHtml::labelTb(Yii::t('orgModule.ViewsPersonProfileHeader', '{1607FCD0-F1F6-4BEC-B058-E1FF5B5B1DCF}'), array('color' => TbHtml::LABEL_COLOR_DEFAULT)); ?>
            </div>
            <div class="status pull-left" id="personStatus">
            <?php
                 $user = $person->getUser();
                 $statusKey = $user->getValidStatus();
                 if(is_null($statusKey)){
                     return;
                 }

                $statusLabels = array(
                    array(
                        'title' => \Yii::t('orgModule.ModelsBlogicPersonManager', '{99541226-7311-423F-97BE-383D7DA13F55}'),
                        'options' => array(
                            'id' => 'userStatus_' . User::STATUS_DELETED,
                            'color' => TbHtml::LABEL_COLOR_DEFAULT,
                            'style' => ($statusKey == User::STATUS_DELETED ? "display: block;" : "display: none;")
                        )
                    ),
                    array(
                        'title' => \Yii::t('orgModule.ModelsBlogicPersonManager', '{D702B52E-C6FE-43D3-A97A-123C12C77860}'),
                        'options' => array(
                            'id' => 'userStatus_' . User::STATUS_ACTIVE,
                            'style' => ($statusKey == User::STATUS_ACTIVE ? "display: block;" : "display:none;")
                        )
                    ),
                    array(
                        'title' => \Yii::t('orgModule.ModelsBlogicPersonManager', '{0EE7D1D6-E33F-40DF-B7E5-C2CFA46B0603}'),
                        'options' => array(
                            'id' => 'userStatus_' . User::STATUS_BLOCKED,
                            'color' => TbHtml::LABEL_COLOR_IMPORTANT,
                            'style' => ($statusKey == User::STATUS_BLOCKED ? "display: block;" : "display: none;")
                        )
                    )
                );

                foreach($statusLabels as $statusLabel){
                    echo TbHtml::labelTb( $statusLabel['title'], $statusLabel['options']);
                }
            ?>

            </div>
            <?php
                $update = '';
                if ($person->getDisableAutoUpdate() == false) {
                    $update = 'style="display: none;"';
                }
            ?>
            <div class="status pull-left" id="personUpdate" <?php echo $update; ?>>
                <?php echo TbHtml::labelTb(Yii::t('orgModule.ViewsPersonProfileHeader', '{2E45DFFC-2337-4ECC-8FF6-5BE656AA9AE2}'), array('color' => TbHtml::LABEL_COLOR_INFO)); ?>
            </div>
    </div>
</div>
<div class="row"></div>
<div class="profile edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
    <div class="edit-data-avatar">
    <?php
        //echo TbHtml::image($person->getAvatar(100,100), false, array("class"=>"img-rounded thumb-100"));
        echo TbHtml::image($person->getAvatar(100,100), false, array("class"=>"img-circle thumb-100"));
        //echo TbHtml::image($person->getAvatar(100,100), false, array("class"=>"img-polaroid thumb-100"));
    ?>
    </div>
    <div class="edit-data-labels" style="width: 250px">
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{0322A24B-8E52-4B60-8E7A-96969F164640}'); ?>:</div>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{645853E4-64B1-405E-839F-DDEB9370AFC8}'); ?>:</div>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{E8E265DF-F6D8-4D9B-9914-89A3EB88C585}'); ?>:</div>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{94BB051D-333B-4C0E-B908-B7E4BF19F44D}'); ?>:</div>
        <?php if (!is_null($person->getSalaryincrement())): ?>
            <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{FFBFC739-04A4-4BD5-A9EA-69130E5C4BBD}'); ?>:</div>
        <?php endif; ?>
        <?php if (trim($person->getSourceTitle() !== '')) { ?>
            <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.default', '{A494EF8D-9369-4331-ABFB-E79340FB5868}'); ?>:</div>
        <?php } ?>
        <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo Yii::t('orgModule.ViewsPersonProfileHeader', '{C8AE27B8-851D-49A9-B2DF-AA1D105E6923}'); ?>:</div>
    </div>
    <div class="edit-data-content" style="width: calc(100% - 350px)">
        <div id="personFullName" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo $person->getFullname(); ?></div>
        <div id="personEmployeeNumber" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo $person->getEmployeeNumber() !== '' ? $person->getEmployeeNumber() : '-'; ?></div>
        <div id="personPositionName" class="dot edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo TbHtml::tag("div", array(), TbHtml::link($person->getPositionName(), Yii::app()->getController()->createUrl("/org/jobTitle/details/id/".TbHtml::encode($person->getPositionId())), array("class" => "template-view")));?></div>
        <div id="personDepartmentFullName" class="dot edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo TbHtml::tag("div", array(), TbHtml::link($person->getDepartmentFullName(), Yii::app()->getController()->createUrl("/org/division/details/id/".TbHtml::encode($person->getDepartmentId())), array("class" => "template-view")));?></div>
        <?php if (!is_null($person->getSalaryincrement())): ?>
            <div class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?= $person->getSalaryincrementRound() ?></div>
        <?php endif; ?>
        <?php if (trim($person->getSourceTitle() !== '')) { ?>
            <div id="jobTitleSource" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
                <?php echo $person->getSourceTitle(); ?>
            </div>
        <?php } ?>
        <div id="personTags" class="edit-data col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding"><?php echo implode(', ', $person->getPersonTagsArray()); ?></div>
    </div>
</div>
<div class="row"></div>
<?php if ($person->getUser()->getId() != Yii::app()->user->id) {return;} ?>
<div class="profile-buttons col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding">
    <?php
        if(!isset($user) || is_null($user)){
            $user = $person->getUser();
        }
        echo TbHtml::hiddenField('personId', $person->getId());
        echo TbHtml::hiddenField('userId', $user->getId());
        echo TbHtml::hiddenField('isValid', (!is_object($user) || is_null($user->getValid()) || $user->getValid() == 1) ? 1 : 0);
        echo TbHtml::button('<i class="tmx-user"></i>&nbsp;'.Yii::t('orgModule.ViewsPersonProfileHeader', '{D7BE97A5-CECD-4BA1-B3A5-25D0797CE421}'), 
            array(
                'class' => 'btn btn-default pull-left',
                'id'=>'openUserProfile',
                'onclick'=>'javascript: return false;'
            ));
    ?>
    <?php
         if (\usr\models\blogic\PrivilegeManager::checkPermission('041', 'org')) {
             echo TbHtml::splitButtonDropdown('<i class="icon tmx-gear"></i>', array(
                 array(
                     'label' => '<div class="dropdownIcon"><i class="tmx-camera"></i></div>' . Yii::t('orgModule.ViewsPersonProfileHeader',
                             '{CA0B04F9-2702-449D-8B5B-DB2235E3D4D7}'),
                     'url' => 'javascript: void(0);',
                     'id' => 'profileActionAvatar'
                 ),
             ), array(
                 'groupOptions' => array('id' => 'profileHeaderGroup'),
                 'class' => 'btn btn-default btn-special',
                 'type' => TbHtml::BUTTON_TYPE_LINK,
                 'url' => 'javascript: void(0);'
             ));
         }
    ?>

</div>
