"""
A* Graph-Based Access Decision Engine
======================================
Uses A* search algorithm to find optimal compliant access paths through an access graph.
Models access decisions as a graph where nodes are access states and edges are transitions.

Key Concepts:
- Nodes: (role, resource, compliance_level, risk_level)
- Edges: Valid access transitions based on policy rules
- Cost: Security risk + computational overhead
- Heuristic: Distance to compliant, low-risk approval state
"""

import heapq
from typing import Tuple, List, Dict, Any, Set, Optional
from dataclasses import dataclass
from enum import Enum
import math


class AccessLevel(Enum):
    """Access classification levels"""
    DENY = 0
    REVIEW = 1
    APPROVE = 2


@dataclass(frozen=True, order=True)
class AccessNode:
    """Represents a state in the access decision graph"""
    role: str
    resource_sensitivity: str
    compliance_level: str
    risk_score: float
    
    def __hash__(self):
        return hash((self.role, self.resource_sensitivity, self.compliance_level, round(self.risk_score, 2)))
    
    def __eq__(self, other):
        if not isinstance(other, AccessNode):
            return False
        return (self.role == other.role and 
                self.resource_sensitivity == other.resource_sensitivity and
                self.compliance_level == other.compliance_level and
                round(self.risk_score, 2) == round(other.risk_score, 2))


@dataclass
class PathStep:
    """A single step in an access decision path"""
    node: AccessNode
    action: str
    cost: float
    reason: str


class AccessGraph:
    """Builds a directed access graph based on RBAC rules and policies"""
    
    def __init__(self):
        """Initialize the access graph"""
        self.nodes: Set[AccessNode] = set()
        self.edges: Dict[AccessNode, List[Tuple[AccessNode, float, str]]] = {}
        self.policy_rules = self._load_default_policies()
    
    def _load_default_policies(self) -> Dict[str, Any]:
        """Load default access control policies"""
        return {
            "role_hierarchy": {
                "admin": 3,
                "manager": 2,
                "developer": 1,
                "analyst": 1,
                "engineer": 1,
                "hr_officer": 1,
                "guest": 0
            },
            "resource_access": {
                "low": ["guest", "developer", "analyst", "engineer", "manager", "admin"],
                "medium": ["developer", "analyst", "engineer", "manager", "admin"],
                "high": ["engineer", "manager", "admin"]
            },
            "compliance_paths": {
                "compliant": {"next_states": ["compliant"], "risk_reduction": 0.1},
                "non_compliant": {"next_states": ["compliant", "review"], "risk_reduction": 0.05}
            }
        }
    
    def build_graph(self, user_profile: Dict[str, Any]) -> AccessNode:
        """
        Build access graph for a specific user request
        
        Returns:
            Initial state node
        """
        role = user_profile.get("role", "guest").lower()
        sensitivity = user_profile.get("access_sensitivity_level", "low").lower()
        compliance = user_profile.get("compliance_status", "non_compliant").lower()
        risk = float(user_profile.get("risk_score", 0.5))
        
        # Normalize compliance status
        compliance = "compliant" if "compliant" in compliance else "non_compliant"
        
        initial_node = AccessNode(
            role=role,
            resource_sensitivity=sensitivity,
            compliance_level=compliance,
            risk_score=risk
        )
        
        # Generate reachable states from initial node
        self._generate_reachable_states(initial_node, user_profile)
        
        return initial_node
    
    def _generate_reachable_states(self, initial_node: AccessNode, user_profile: Dict[str, Any]):
        """Generate all reachable states from initial node"""
        visited = set()
        queue = [initial_node]
        max_iterations = 50
        iteration = 0
        
        while queue and iteration < max_iterations:
            iteration += 1
            current = queue.pop(0)
            
            if current in visited:
                continue
            
            visited.add(current)
            self.nodes.add(current)
            self.edges[current] = []
            
            # Generate possible transitions
            next_states = self._get_next_states(current, user_profile)
            
            for next_node, cost, reason in next_states:
                if next_node not in visited:
                    queue.append(next_node)
                    self.edges[current].append((next_node, cost, reason))
    
    def _get_next_states(self, node: AccessNode, user_profile: Dict[str, Any]) -> List[Tuple[AccessNode, float, str]]:
        """Get possible transitions from current node"""
        next_states = []
        role_level = self.policy_rules["role_hierarchy"].get(node.role, 0)
        
        # Transition 1: Improve compliance (risk reduction)
        if node.compliance_level == "non_compliant":
            improved_node = AccessNode(
                role=node.role,
                resource_sensitivity=node.resource_sensitivity,
                compliance_level="compliant",
                risk_score=max(0, node.risk_score - 0.15)
            )
            cost = 1.0  # Cost of compliance improvement
            next_states.append((improved_node, cost, "Improve compliance status"))
        
        # Transition 2: Request escalation (higher privilege)
        if role_level < 3:  # Not admin
            escalated_roles = {0: "developer", 1: "manager", 2: "admin"}
            next_role = escalated_roles.get(role_level + 1, node.role)
            escalated_node = AccessNode(
                role=next_role,
                resource_sensitivity=node.resource_sensitivity,
                compliance_level=node.compliance_level,
                risk_score=node.risk_score + 0.05
            )
            cost = 2.0  # Cost of escalation
            next_states.append((escalated_node, cost, f"Request role escalation to {next_role}"))
        
        # Transition 3: Risk mitigation (secondary auth, VPN, etc.)
        if node.risk_score > 0.4:
            mitigated_node = AccessNode(
                role=node.role,
                resource_sensitivity=node.resource_sensitivity,
                compliance_level=node.compliance_level,
                risk_score=max(0, node.risk_score - 0.2)
            )
            cost = 1.5
            next_states.append((mitigated_node, cost, "Apply risk mitigation (MFA, VPN, etc.)"))
        
        # Transition 4: Direct access attempt (only if initial state doesn't qualify)
        # This forces A* to explore paths rather than settling on initial state
        if self._can_access(node) and node.risk_score < 0.3:
            next_states.append((node, 0, "Direct access - conditions satisfied"))
        
        return next_states
    
    def _can_access(self, node: AccessNode) -> bool:
        """Check if node satisfies access conditions"""
        allowed_roles = self.policy_rules["resource_access"].get(
            node.resource_sensitivity, []
        )
        
        if node.role not in allowed_roles:
            return False
        
        if node.risk_score > 0.66:
            return False
        
        if "non" in node.compliance_level and node.resource_sensitivity in ["medium", "high"]:
            return False
        
        return True


class AStarEngine:
    """A* search algorithm for optimal access paths"""
    
    def __init__(self, graph: AccessGraph):
        self.graph = graph
    
    def _heuristic(self, node: AccessNode, goal: AccessNode) -> float:
        """
        Heuristic function for A* search
        Estimates distance to goal (approved access)
        """
        cost = 0.0
        
        # Risk distance
        cost += abs(node.risk_score - goal.risk_score) * 2.0
        
        # Compliance distance
        if node.compliance_level != goal.compliance_level:
            cost += 1.0
        
        # Role level distance
        role_hierarchy = self.graph.policy_rules["role_hierarchy"]
        current_level = role_hierarchy.get(node.role, 0)
        goal_level = role_hierarchy.get(goal.role, 0)
        cost += abs(current_level - goal_level) * 1.5
        
        return cost
    
    def find_optimal_path(
        self,
        start: AccessNode,
        user_profile: Dict[str, Any],
        max_path_length: int = 5
    ) -> Tuple[List[PathStep], str, float]:
        """
        Find optimal access decision path using A* search
        
        Args:
            start: Initial access state
            user_profile: User's profile data
            max_path_length: Maximum steps to consider
        
        Returns:
            (path_steps, decision, total_cost)
        """
        
        # Define goal states
        goal_approve = AccessNode(
            role=start.role,
            resource_sensitivity=start.resource_sensitivity,
            compliance_level="compliant",
            risk_score=0.0
        )
        
        goal_review = AccessNode(
            role=start.role,
            resource_sensitivity=start.resource_sensitivity,
            compliance_level="non_compliant",
            risk_score=0.33
        )
        
        # Run A* search to approval path
        approve_path = self._astar_search(start, goal_approve, max_path_length)
        
        # Run A* search to review path (fallback)
        review_path = self._astar_search(start, goal_review, max_path_length)
        
        # Evaluate paths and decide
        if approve_path and approve_path[0] < 3.0:
            decision = "approve"
            path = approve_path[1]
        elif review_path and review_path[0] < 5.0:
            decision = "review"
            path = review_path[1]
        else:
            decision = "deny"
            path = [(start, "Path analysis complete", 0.0, "No acceptable path found")]
        
        path_steps = [
            PathStep(node, action, cost, reason)
            for node, action, cost, reason in path
        ]
        
        total_cost = sum(p.cost for p in path_steps)
        
        return path_steps, decision, total_cost
    
    def _astar_search(
        self,
        start: AccessNode,
        goal: AccessNode,
        max_depth: int
    ) -> Optional[Tuple[float, List[Tuple[AccessNode, str, float, str]]]]:
        """
        Core A* search implementation
        
        Returns:
            (total_cost, path) or None if no path found
        """
        open_set = []
        came_from = {}
        g_score = {start: 0}
        f_score = {start: self._heuristic(start, goal)}
        
        heapq.heappush(open_set, (f_score[start], id(start), start))
        closed_set = set()
        
        while open_set:
            current_f, _, current = heapq.heappop(open_set)
            
            if current in closed_set:
                continue
            
            closed_set.add(current)
            
            # Check if reached goal (but NOT on the very first iteration - must take at least 1 transition)
            if self._is_goal(current, goal) and current in came_from:
                return self._reconstruct_path(start, current, came_from)
            
            # Check depth limit
            if g_score[current] >= max_depth:
                continue
            
            # Explore neighbors
            if current in self.graph.edges:
                for neighbor, cost, reason in self.graph.edges[current]:
                    if neighbor in closed_set:
                        continue
                    
                    tentative_g = g_score[current] + cost
                    
                    if neighbor not in g_score or tentative_g < g_score[neighbor]:
                        came_from[neighbor] = (current, cost, reason)
                        g_score[neighbor] = tentative_g
                        f_score[neighbor] = tentative_g + self._heuristic(neighbor, goal)
                        heapq.heappush(open_set, (f_score[neighbor], id(neighbor), neighbor))
        
        return None
    
    def _is_goal(self, current: AccessNode, goal: AccessNode) -> bool:
        """Check if current node satisfies goal criteria"""
        return (current.risk_score <= goal.risk_score and
                current.compliance_level == goal.compliance_level)
    
    def _reconstruct_path(
        self,
        start: AccessNode,
        current: AccessNode,
        came_from: Dict
    ) -> Tuple[float, List[Tuple[AccessNode, str, float, str]]]:
        """Reconstruct the path taken by A* search"""
        path = [(current, "Goal reached", 0.0, "Optimal state achieved")]
        total_cost = 0.0
        
        while current in came_from:
            prev, cost, reason = came_from[current]
            path.append((prev, reason, cost, f"Transition: {reason}"))
            total_cost += cost
            current = prev
        
        path.reverse()
        return total_cost, path


def analyze_access_with_astar(user_profile: Dict[str, Any]) -> Dict[str, Any]:
    """
    Main function: Analyze user access request using A* algorithm
    
    Args:
        user_profile: User data dictionary
    
    Returns:
        Decision report with path analysis
    """
    # Build access graph
    graph = AccessGraph()
    initial_node = graph.build_graph(user_profile)
    
    # Run A* search
    engine = AStarEngine(graph)
    path_steps, decision, total_cost = engine.find_optimal_path(initial_node, user_profile)
    
    # Format report
    path_description = " → ".join([f"{s.node.role}@{s.node.compliance_level}" for s in path_steps])
    
    return {
        "decision": decision,
        "algorithm": "A* Graph Search",
        "path_length": len(path_steps),
        "total_cost": round(total_cost, 3),
        "path_description": path_description,
        "path_steps": [
            {
                "role": s.node.role,
                "compliance": s.node.compliance_level,
                "risk": round(s.node.risk_score, 3),
                "action": s.action,
                "cost": round(s.cost, 3),
                "reason": s.reason
            }
            for s in path_steps
        ],
        "recommendation": f"Use A* optimization for {decision.upper()} decision"
    }
