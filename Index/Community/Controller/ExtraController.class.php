<?php
/**
 * drunk , fix later
 * Created by Magic.
 * User: jiaying
 * Datetime: 08/11/2016 22:07
 */

namespace Community\Controller;
use Constant\Constants\Chapter;

/**
 * 本控制器主要用于一些特殊情况下要显示的页面, just for fun
 * 就不按照框架的设计去做了,所有的数据层也写在这里.
 * Class ExtraController
 * @package Community\Controller
 */
class ExtraController extends TemplateController
{
    public function _initialize() {
        $this->isNeedLogin = false;
        parent::_initialize();
    }

    private static $DEFAULTRATE = 0;

    // 计分比例
    private $scorePercent = array(
        'english' => 7,
        'chinese' => 1,
        'person'  => 2
    );

    public function jiaying() {
        $chapters = Chapter::getConstant();
        dbg($chapters);

        $chapter = Chapter::getById(3);
        dbg($chapter);

        $priorities = Chapter::getIdBiggerPriority(3);
        dbg($priorities);
    }

    public function rank() {
        // 获取所有注册的学生
        $students = $this->getAllSignUpStudent();
        $userIds = array();
        foreach ($students as $_student) {
            $userIds[] = $_student['user_id'];
        }

        // 获取这些学生所有答对的题的个数
        $userAllSolved = $this->getUserSolved($userIds);

        // 获取第二页做的题目数量
        $userEnProblemSolved = $this->getEnglish2ndPageSolved($userIds);

        foreach ($students as &$student) {
            $_userId = $student['user_id'];
            $englishNum = isset($userEnProblemSolved[$_userId]) ? intval($userEnProblemSolved[$_userId]) : 0;
            $chineseNum = $userAllSolved[$_userId] - $englishNum;
            $student['chineseNum'] = $chineseNum;
            $student['englishNum'] = $englishNum;
            $student['person'] = empty($student['seatnum']) ? self::$DEFAULTRATE : $student['seatnum'];
            $score = $chineseNum * $this->scorePercent['chinese']
                + $englishNum * $this->scorePercent['english']
                + $student['person'] * $this->scorePercent['person'];
            $student['score'] = $score;
            if ($student['stusex'] == 'F') {
                $student['score'] = $student['score'] * 1.05;
            }
        }
        unset($student);

        $students = myMultiSort($students, 'score', SORT_DESC);
        $rank = 0; $preScore = -1; $cnt = 0;
        foreach ($students as &$student) {
            if ($student['score'] != $preScore) {
                $rank = $rank + $cnt + 1;
                $preScore = $student['score'];
                $cnt = 0;
            } else {
                $cnt++;
            }
            $student['rank'] = $rank;
        }
        unset($student);

        $this->zadd("students", $students);
        $this->auto_display(null, false);
    }

    private function getAllSignUpStudent() {
        // contest id is 1753
        $sql = "select user_id, sturealname as `name`, seatnum, stusex from contestreg where contest_id = 1753";
        $students = M()->query($sql);
        return $students;
    }

    private function getUserSolved($userIds) {
        $userStr = implode('\',\'', $userIds);
        $userStr = '\'' . $userStr . '\'';
        $sql = "select user_id, solved from users where user_id in ( $userStr )";
        $userSolved = M()->query($sql);
        $userAllSolved = array();
        foreach ($userSolved as $solved) {
            $userAllSolved[$solved['user_id']] = $solved['solved'];
        }
        return $userAllSolved;
    }

    private function getEnglish2ndPageSolved($userIds) {
        $userStr = implode('\',\'', $userIds);
        $userStr = '\'' . $userStr . '\'';
        $sql = "select user_id, count(distinct(problem_id)) as num from solution where " .
            "problem_id >= 1145 and problem_id <= 1191 and result = 4 and " .
            "user_id in ($userStr) group by user_id";
        $problemSolved = M()->query($sql);

        $userEnProblemSolved = array();
        foreach ($problemSolved as $solved) {
            $userEnProblemSolved[$solved['user_id']] = $solved['num'];
        }
        return $userEnProblemSolved;
    }
}