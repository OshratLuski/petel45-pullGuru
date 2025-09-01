/* eslint-disable babel/no-unused-expressions */
/* eslint-disable max-len */
/* eslint-disable array-callback-return */
/* eslint-disable no-unreachable */
/* eslint-disable babel/object-curly-spacing */
/* eslint-disable no-empty-function */
/* eslint-disable no-multiple-empty-lines */
/* eslint-disable babel/semi */
/* eslint-disable brace-style */
/* eslint-disable block-spacing */
/* eslint-disable max-statements-per-line */
/* eslint-disable semi-spacing */
/* eslint-disable no-debugger */
/* eslint-disable space-before-blocks */
/* eslint-disable no-trailing-spaces */
/* eslint-disable space-before-function-paren */
/* eslint-disable block-scoped-var */
/* eslint-disable no-empty */
/* eslint-disable no-undef */
/* eslint-disable no-console */
/* eslint-disable no-dupe-keys */
/* eslint-disable no-unused-vars */
/* eslint-disable key-spacing */
/* eslint-disable spaced-comment */
/* eslint-disable capitalized-comments */

import Ajax from "core/ajax";

export default {
  init: function (qid, qaid, maxmark, minfraction, maxfraction, usageid, slot) {
    Ajax.call([
      {
        methodname: "qtype_essayrubric_get_grades",
        args: {
          qaid: qaid,
          qid: qid,
        },
        done: function (result) {
          result = JSON.parse(result.result);
          render(result);
        },
        fail: {},
      },
    ]);

    const render = (data) => {
      // Hide comment block. Not needed.
      // let fitems = document.querySelectorAll(".fitem");
      // fitems.forEach((fitem) => {
      //   let fitemTitle = fitem.querySelector('[id$="-comment_id"]');
      //   if (fitemTitle) {
      //     fitem.style.display = "none";
      //   }
      // });

      const manualgradingform = document.getElementById("manualgradingform");

      manualgradingform.addEventListener("submit", (event) => {
        // Store grades
        Ajax.call([
          {
            methodname: "qtype_essayrubric_store_grades",
            args: {
              data: JSON.stringify(data),
              qaid: qaid,
            },
            done: function (result) {},
            fail: {},
          },
        ]);
      });

      const gradeInputs = document.getElementsByClassName("gradeinput");
      for (const gradeInput of gradeInputs) {
        gradeInput.addEventListener("change", (event) => {
          recalcGrade(manualgradingform, qaid);
        });
      }

      const recalcGrade = (manualgradingform, qaid) => {
        // const indicators = {};
        for (const input of manualgradingform.querySelectorAll(
          '[name^="indicator_' + qaid + '_"]'
        )) {
          const indicatorId = input.name.split("_")[2];
          // indicators[indicatorId] = indicatorValue;
          data.isgradestypescalar = Number(data.isgradestypescalar);
          if (data.isgradestypescalar) {
            const indicatorValue = input.value;
            data.indicatorlist[indicatorId].checked = +indicatorValue;
            data.indicatorlist[indicatorId].weightedGrade =
              (indicatorValue / 5) *
              data.indicatorlist[indicatorId].normalizedWeight;
            data.indicatorlist[indicatorId].normalizedGrade =
              data.indicatorlist[indicatorId].weightedGrade == 0
                ? 0
                : (data.indicatorlist[indicatorId].weightedGrade /
                    data.indicatorlist[indicatorId].normalizedWeight) *
                  100;
          } else {
            const indicatorValue = input.checked;
            data.indicatorlist[indicatorId].checked = +indicatorValue;
            data.indicatorlist[indicatorId].weightedGrade =
              indicatorValue * data.indicatorlist[indicatorId].normalizedWeight;
            data.indicatorlist[indicatorId].normalizedGrade =
              data.indicatorlist[indicatorId].weightedGrade === 0
                ? 0
                : (indicatorValue *
                    100 *
                    data.indicatorlist[indicatorId].normalizedWeight) /
                  data.indicatorlist[indicatorId].weightedGrade;
          }
        }

        let questionGradeMarkEl = document.getElementById(
          "q" + usageid + ":" + slot + "_-mark"
        );

        let totalGrade = 0;

        data.indicatorlist.forEach((element) => {
          let weighted = element.weightedGrade;
          totalGrade += weighted;
        });

        totalGrade = (totalGrade * maxmark) / 100;

        totalGrade = Number(totalGrade.toFixed(2));
        questionGradeMarkEl.value = totalGrade;

        data.grade = totalGrade;
      };

      let totalWeight = 0;
      let totalIndicators = data.indicatorlist.length;

      data.indicatorlist = data.indicatorlist.map((ind, key) => {
        let indWeight =
          ind.weight == undefined || ind.weight == "" ? 0 : +ind.weight;
        totalWeight += indWeight;
        ind.weight = indWeight;
        return ind;
      });

      data.indicatorlist = data.indicatorlist.map((ind, key) => {
        ind.normalizedWeight = (ind.weight / totalWeight) * 100;
        return ind;
      });
    };
  },
};
