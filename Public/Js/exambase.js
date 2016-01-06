/**
 * Created by jiaying on 15/11/14.
 */
$(function () {
    $(".upd-stuprogram").click(function(){
        var eid = $("#examid").val();
        var pid = $(this).data('pid');
        var panel = "collapseExample" + pid;
        var accepted = $("#" + panel).data('accepted');
        if (accepted != 1) {
            $.ajax({
                url: saveprogram,
                type: "POST",
                dataType: "html",
                data: 'eid=' + eid + '&pid=' + pid,
                success: function (data) {
                    if (data == 4) {
                        $("#" + panel).data('accepted', 1).html("此题你已正确!");//slideUp("slow", function(){$(this).remove();});
                    }
                },
                error: function () {
                    console.log('error update program');
                }
            });
        }
    });
    $("#savePaper").click(function(){
        if (questionType == 1) {
            savePaper(chooseSaveUrl, "chooseExam");
        }  else if (questionType == 2) {
            savePaper(judgeSaveUrl, "judgeExam");
        } else if (questionType == 3) {
            savePaper(fillSaveUrl, "fillExam");
        }
    });
    $(".submitcode").click(function(){
        var pid = $(this).data('programid');
        var eid = $("#examid").val();
        var span = "span" + pid;
        var code = "code" + pid;
        var language = "language" + pid;
        submitcode(span, code, language, pid, eid);
    });
    $(".updateresult").click(function(){
        var pid = $(this).data('proid');
        var eid = $("#examid").val();
        var span = "span" + pid;
        updateresult(this, span, pid, eid);
    });
    antiCheat();
    GetRTime();
});

function submitChoosePaper() {
    $("#chooseExam").submit();
}

function submitJudgePaper() {
    $("#judgeExam").submit();
}

function submitFillPaper() {
    $("#fillExam").submit();
}

function submitProgramPaper() {
    $("#programExam").submit();
}

function examFormSubmit() {
    var problemType = $("#problemType").val();
    if (problemType == 1) {
        submitChoosePaper();
    } else if (problemType == 2) {
        submitJudgePaper();
    } else if (problemType == 3) {
        submitFillPaper();
    } else {
        alert("page error, please refresh~");
    }
}

function savePaper(saveUrl, formId) {
    $.ajax({
        url: saveUrl,
        type: "POST",
        dataType: "html",
        data: $("#" + formId).serialize(),
        success: function(e) {
            "ok" == e ? ($("#saveover").html("[已保存]"), setTimeout(function() {
                $("#saveover").html("")
            }, 6e3)) : $("#saveover").html(e)
        },
        error: function() {
            alert("something error when you save")
        }
    });
}

function antiCheat() {
    $("body").keydown(function (event) {
        if (event.keyCode == 116) {
            event.returnValue = false;
            alert("当前设置不允许使用F5刷新键");
            return false;
        }
        if( (event.ctrlKey) && (event.keyCode == 83) ) {
            event.returnValue = false;
            return false;
        }
        //if (event.ctrlKey) {
        //    event.returnValue = false;
        //    return false;
        //}
        //if (event.altKey) {
        //    event.returnValue = false;
        //    return false;
        //}
        if (event.keyCode == 123) {
            event.returnValue = false;
            alert("当前设置不允许使用F12键");
            return false;
        }
    });
    //}).mouseleave(function () {
    //    alert('xxx');
    //});
}

var isalert = false;
var runtimes = 0;
function GetRTime() {
    var nMS = left - runtimes * 1000;
    if (nMS > 0) {
        var nH = Math.floor(nMS / (1000 * 60 * 60));
        var nM = Math.floor(nMS / (1000 * 60)) % 60;
        var nS = Math.floor(nMS / 1000) % 60;
        var nHstr = (nH >= 10 ? nH : "0" + nH);
        var nMstr = (nM >= 10 ? nM : "0" + nM);
        var nSstr = (nS >= 10 ? nS : "0" + nS);
        $("#RemainH").html(nHstr);
        $("#RemainM").html(nMstr);
        $("#RemainS").html(nSstr);
        if (nMS <= 5 * 60 * 1000 && isalert == false) {
            $('.tixinga').css("color", "red");
            $('.tixingb').css("color", "red");
            isalert = true;
        }
        if (nMS > 0 && nMS <= 1000) {
            switch (questionType) {
                case 1 :
                    submitChoosePaper();
                    break;
                case 2 :
                    submitJudgePaper();
                    break;
                case 3 :
                    submitFillPaper();
                    break;
                case 4 :
                    submitProgramPaper();
            }
        }

        if (nMS % savetime == 0 && nMS > savetime) {
            switch (questionType) {
                case 1 :
                    savePaper(chooseSaveUrl, "chooseExam");
                    break;
                case 2 :
                    savePaper(judgeSaveUrl, "judgeExam");
                    break;
                case 3 :
                    savePaper(fillSaveUrl, "fillExam");
                    break;
            }
        }
        runtimes++;
        setTimeout("GetRTime()", 1000);
    }
}