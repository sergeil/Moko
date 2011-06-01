

namespace <?php echo $namespace ?>;

<?php echo $targetDocBlock ?>
class <?php echo $className ?> <?php echo $targetRelationship ?> \<?php echo $targetName; ?>
{
    static public $____callbacks = array();
    static public $____invocationCounters = array();

    <?php if ($omitConstructor): ?>
    public function __construct()
    {
    }
    <?php endif ?>

    <?php foreach($methods as $methodName=>$methodDef): ?> 
    <?php echo $methodDef['docBlock'] ?> 
    <?php echo implode(' ', $methodDef['modifiers']) ?> function <?php echo $methodName ?>(<?php echo implode(',', $methodDef['params'])?>)
    {
        if (!isset(self::$____invocationCounters['<?php echo $methodName ?>'])) {
            self::$____invocationCounters['<?php echo $methodName ?>'] = 0;
        }
        self::$____invocationCounters['<?php echo $methodName ?>']++;

        <?php if (!$methodDef['isExplicetelyDefined'] && !$suppressUnexpectedInteractionExceptions): ?>
        throw new \Moko\UnexpectedInteractionException(
            "Method '<?php echo $methodName?>' from mock of \<?php echo $targetName ?> is not expected to be invoked."
        );
        <?php else:?>
        $callback = self::$____callbacks['<?php echo $methodName ?>'];
        return $callback(
            <?php echo $methodDef['isStatic'] ? '__CLASS__' : '$this' ?>
            <?php
            if (sizeof($methodDef['paramNames'])) {
                echo ', ';
                echo implode(' ,', $methodDef['paramNames']);
            }
            ?>
        );
        <?php endif ?>
    }
    <?php endforeach; ?>
}
