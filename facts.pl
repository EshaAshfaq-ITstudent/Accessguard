% =========================
% ===== FACTS FILE ========
% =========================

:- discontiguous user/1.
:- discontiguous role/2.
:- discontiguous department/2.
:- discontiguous risk_score/2.
:- discontiguous compliance/2.
:- discontiguous failed_logins/2.
:- discontiguous sensitivity/2.
:- discontiguous time_of_day/2.
:- discontiguous device_type/2.


% ── User u001 ──────────────────────────────────────────
user(u001).
role(u001, manager).
department(u001, engineering).
risk_score(u001, high).
compliance(u001, non_compliant).
failed_logins(u001, 4).
sensitivity(u001, high).
time_of_day(u001, night).
device_type(u001, mobile).

% ── User u002 ──────────────────────────────────────────
user(u002).
role(u002, analyst).
department(u002, finance).
risk_score(u002, low).
compliance(u002, compliant).
failed_logins(u002, 0).
sensitivity(u002, low).
time_of_day(u002, morning).
device_type(u002, desktop).

% ── User u003 ──────────────────────────────────────────
user(u003).
role(u003, admin).
department(u003, it).
risk_score(u003, low).
compliance(u003, compliant).
failed_logins(u003, 0).
sensitivity(u003, low).
time_of_day(u003, morning).
device_type(u003, desktop).

% ── User u004 ──────────────────────────────────────────
user(u004).
role(u004, developer).
department(u004, engineering).
risk_score(u004, medium).
compliance(u004, compliant).
failed_logins(u004, 2).
sensitivity(u004, medium).
time_of_day(u004, afternoon).
device_type(u004, laptop).

% ── User u005 ──────────────────────────────────────────
user(u005).
role(u005, guest).
department(u005, hr).
risk_score(u005, high).
compliance(u005, non_compliant).
failed_logins(u005, 5).
sensitivity(u005, high).
time_of_day(u005, night).
device_type(u005, mobile).

% ── User u006 ──────────────────────────────────────────
user(u006).
role(u006, engineer).
department(u006, security).
risk_score(u006, low).
compliance(u006, compliant).
failed_logins(u006, 1).
sensitivity(u006, medium).
time_of_day(u006, morning).
device_type(u006, desktop).

% ── User u007 ──────────────────────────────────────────
user(u007).
role(u007, hr_officer).
department(u007, hr).
risk_score(u007, medium).
compliance(u007, non_compliant).
failed_logins(u007, 3).
sensitivity(u007, high).
time_of_day(u007, evening).
device_type(u007, laptop).

% ── User u008 ──────────────────────────────────────────
user(u008).
role(u008, manager).
department(u008, finance).
risk_score(u008, low).
compliance(u008, compliant).
failed_logins(u008, 0).
sensitivity(u008, low).
time_of_day(u008, morning).
device_type(u008, desktop).

% ── User u009 ──────────────────────────────────────────
user(u009).
role(u009, developer).
department(u009, operations).
risk_score(u009, high).
compliance(u009, non_compliant).
failed_logins(u009, 6).
sensitivity(u009, high).
time_of_day(u009, night).
device_type(u009, mobile).

% ── User u010 ──────────────────────────────────────────
user(u010).
role(u010, analyst).
department(u010, legal).
risk_score(u010, low).
compliance(u010, compliant).
failed_logins(u010, 0).
sensitivity(u010, low).
time_of_day(u010, afternoon).
device_type(u010, desktop).
