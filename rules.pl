:- discontiguous user/1.
:- discontiguous role/2.
:- discontiguous department/2.
:- discontiguous risk_score/2.
:- discontiguous compliance/2.
:- discontiguous failed_logins/2.
:- discontiguous sensitivity/2.
:- discontiguous time_of_day/2.
:- discontiguous device_type/2.
:- discontiguous why_review/2.
:- discontiguous why_denied/2.
:- discontiguous why_approved/2.
:- discontiguous access_decision/2.


% =========================
% ===== USER FACTS ========
% =========================

user(u001).
role(u001, manager).
department(u001, engineering).
risk_score(u001, high).
compliance(u001, non_compliant).
failed_logins(u001, 4).
sensitivity(u001, high).
time_of_day(u001, night).

user(u002).
role(u002, analyst).
department(u002, finance).
risk_score(u002, low).
compliance(u002, compliant).
failed_logins(u002, 0).
sensitivity(u002, low).
time_of_day(u002, morning).


% =========================
% ===== RULES ============
% =========================

% DENY ACCESS
deny_access(User) :-
    risk_score(User, high),
    compliance(User, non_compliant).

deny_access(User) :-
    failed_logins(User, N),
    N >= 3,
    sensitivity(User, high).

deny_access(User) :-
    compliance(User, non_compliant),
    sensitivity(User, high).


% REVIEW ACCESS
review_access(User) :-
    time_of_day(User, night),
    sensitivity(User, high).

review_access(User) :-
    failed_logins(User, N),
    N =:= 2,
    risk_score(User, medium).


% APPROVE ACCESS
approve_access(User) :-
    compliance(User, compliant),
    risk_score(User, low),
    failed_logins(User, N),
    N < 3,
    sensitivity(User, low).

approve_access(User) :-
    role(User, admin),
    compliance(User, compliant),
    risk_score(User, low).

approve_access(User) :-
    role(User, manager),
    department(User, engineering),
    compliance(User, compliant),
    risk_score(User, low),
    failed_logins(User, 0).


% =========================
% ===== FINAL DECISION ====
% =========================

access_decision(User, deny) :-
    deny_access(User).

access_decision(User, review) :-
    \+ deny_access(User),
    review_access(User).

access_decision(User, approve) :-
    \+ deny_access(User),
    approve_access(User).

% fallback
access_decision(User, review) :-
    \+ deny_access(User),
    \+ approve_access(User).


% =========================
% ===== HELPER RULES ======
% =========================

high_risk_user(User) :-
    risk_score(User, high),
    compliance(User, non_compliant).

suspicious_user(User) :-
    failed_logins(User, N),
    N >= 3.

safe_user(User) :-
    compliance(User, compliant),
    risk_score(User, low),
    failed_logins(User, N),
    N < 3.

why_denied(User, reason(high_risk_and_non_compliant)) :-
    risk_score(User, high),
    compliance(User, non_compliant).

why_denied(User, reason(excess_failed_logins)) :-
    failed_logins(User, N),
    N >= 3.

why_denied(User, reason(non_compliant_high_sensitivity)) :-
    compliance(User, non_compliant),
    sensitivity(User, high).

why_review(User, reason(night_access_high_sensitivity)) :-
    time_of_day(User, night),
    sensitivity(User, high).

why_review(User, reason(failed_attempts_medium_risk)) :-
    failed_logins(User, 2),
    risk_score(User, medium).

why_review(User, reason(borderline_case)) :-
    \+ deny_access(User),
    \+ approve_access(User).

why_approved(User, reason(low_risk_compliant)) :-
    compliance(User, compliant),
    risk_score(User, low),
    failed_logins(User, N),
    N < 3.

explain(User, Decision, Reason) :-
    access_decision(User, Decision),
    ( why_denied(User, Reason)
    ; why_review(User, Reason)
    ; why_approved(User, Reason)
    ).
