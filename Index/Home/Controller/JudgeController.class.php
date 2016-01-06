<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:42
 */

namespace Home\Controller;

use Home\Model\AnswerModel;
use Home\Model\ExamadminModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\ProblemServiceModel;
use Teacher\Model\StudentBaseModel;

// TODO 暂时未开放此类,主要为了将各题目模型分隔
class JudgeController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
        $this->addExamBaseInfo();
        if ($this->checkHasScore('judgesum')) {
            $this->alertError('该题型你已经交卷,不能再查看', $this->navigationUrl);
        }
    }

    public function index() {

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $judgearr = ExamServiceModel::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgeans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        $judgesx = ExamadminModel::instance()->getproblemsx($this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('judgearr', $judgearr);
        $this->zadd('judgesx', $judgesx);
        $this->zadd('judgeans', $judgeans);
        $this->zadd('problemType', JudgeBaseModel::JUDGE_PROBLEM_TYPE);

        $this->auto_display('Exam:judge', 'exlayout');
    }

    public function saveAnswer() {
        AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
        echo 'ok';
    }

    public function submitPaper() {
        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $jright = AnswerModel::instance()->answersave($this->userInfo['user_id'], $this->examId, JudgeBaseModel::JUDGE_PROBLEM_TYPE, false);
        $inarr['judgesum'] = $jright * $allscore['judgescore'];
        StudentBaseModel::instance()->submitExamPaper(
            $this->userInfo['user_id'], $this->examId, $inarr);
        redirect(U('Home/Question/navigation', array('eid' => $this->examId)));
    }
}