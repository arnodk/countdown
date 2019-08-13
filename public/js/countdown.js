window.onload = function() {
    let countdown = new Vue({
        el: '#numbers',
        data:  {
            sharedState: store.state
        }
    });
    document.getElementById("numbers").style.display = "block";

    ds.getProblem();
};

var store = {
    debug: true,
    state: {
        target: '',
        numbers: ['','','','','',''],
        result:'',
        proof:'',
        score:0,
        working:false
    },
    setNumbers (aNumbers) {
        this.state.numbers = aNumbers
    },
    setTarget (iTarget) {
        this.state.target = parseInt(iTarget);
    },
    setResult (iResult) {
        this.state.result = parseInt(iResult);
    },
    setProof (sProof) {
        this.state.proof = sProof;
    },
    getNumbers() {
        return this.state.numbers;
    },
    getTarget() {
        return this.state.target;
    },
    setWorking (bWorking) {
        this.state.working = bWorking;
    },
    setScore(iScore) {
        this.state.score = parseInt(iScore);
    }
};

class Ds {
    constructor() {
        this.token = '';
        this.base = '';
    }

    apiUrl(sAction,sParam) {
        let sUrl = this.base + "/countdown/" + sAction;
        if (typeof sParam != 'undefined') sUrl = sUrl + "/" + sParam;
        return sUrl;
    }

    apiCall(sAction, oData={}, fResult) {
        var sUrl = this.apiUrl(sAction);
        this.postData(sUrl,oData).then(fResult);
    }

    postData(url = '', data = {}) {
        return fetch(url, {
            method: 'POST',
            mode: 'same-origin',
            cache: 'no-cache',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            referrer: 'no-referrer',
            body: JSON.stringify(data)
        })
            .then(response => response.json());
    }

    getProblem() {
        let me = this;
        this.apiCall('generateProblem',{},function(oResult) {
            store.setNumbers(oResult.numbers);
            store.setTarget(oResult.target);
            me.solveProblem();
        })
    }

    solveProblem() {
        // start spinner
        store.setWorking(true);
        this.apiCall('solveProblem/' + store.getTarget() + '/' + store.getNumbers().join(","),{},function(oResult) {
            // stop spinner
            store.setWorking(false);
            store.setResult(oResult.result);
            store.setProof(oResult.proof);
            store.setScore(oResult.score);
        })
    }
}

var ds = new Ds();
