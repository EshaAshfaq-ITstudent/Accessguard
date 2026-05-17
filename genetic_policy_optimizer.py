"""
Genetic Algorithm for Access Policy Optimization
=================================================
Evolves access control policies through generations to optimize the balance
between security, usability, and compliance.

Key Concepts:
- Population: Multiple policy rule sets
- Fitness: Security score + usability score - compliance violations
- Crossover: Combine two parent policies
- Mutation: Randomly modify policy parameters
- Selection: Tournament selection of fittest policies
"""

import random
from typing import List, Dict, Tuple, Any
from dataclasses import dataclass
import json


@dataclass
class PolicyRule:
    """Represents a single access control rule"""
    name: str
    condition: Dict[str, Any]  # e.g., {"role": "admin", "risk_max": 0.5}
    action: str  # "approve", "deny", "review"
    priority: int  # Higher = evaluated first
    weight: float  # Importance in fitness calculation
    
    def to_dict(self) -> Dict[str, Any]:
        return {
            "name": self.name,
            "condition": self.condition,
            "action": self.action,
            "priority": self.priority,
            "weight": self.weight
        }
    
    @staticmethod
    def from_dict(d: Dict[str, Any]) -> "PolicyRule":
        return PolicyRule(
            name=d["name"],
            condition=d["condition"],
            action=d["action"],
            priority=d["priority"],
            weight=d["weight"]
        )


@dataclass
class AccessPolicy:
    """Complete access control policy (chromosome in GA)"""
    rules: List[PolicyRule]
    generation: int = 0
    fitness_score: float = 0.0
    
    def copy(self) -> "AccessPolicy":
        """Create a deep copy of this policy"""
        return AccessPolicy(
            rules=[PolicyRule(
                name=r.name,
                condition=r.condition.copy(),
                action=r.action,
                priority=r.priority,
                weight=r.weight
            ) for r in self.rules],
            generation=self.generation,
            fitness_score=self.fitness_score
        )
    
    def evaluate_request(self, user_profile: Dict[str, Any]) -> Tuple[str, str]:
        """
        Evaluate a user request using this policy
        
        Returns:
            (decision, matched_rule_name)
        """
        # Sort by priority
        sorted_rules = sorted(self.rules, key=lambda r: r.priority, reverse=True)
        
        for rule in sorted_rules:
            if self._matches_condition(user_profile, rule.condition):
                return rule.action, rule.name
        
        return "review", "default_review_rule"
    
    def _matches_condition(self, user_profile: Dict[str, Any], condition: Dict[str, Any]) -> bool:
        """Check if user profile matches rule condition"""
        for key, value in condition.items():
            if key == "risk_max":
                if float(user_profile.get("risk_score", 0)) > value:
                    return False
            elif key == "risk_min":
                if float(user_profile.get("risk_score", 0)) < value:
                    return False
            elif key == "role":
                if user_profile.get("role", "").lower() != str(value).lower():
                    return False
            elif key == "department":
                if user_profile.get("department", "").lower() != str(value).lower():
                    return False
            elif key == "compliance":
                compliance = str(user_profile.get("compliance_status", "")).lower()
                required = str(value).lower()
                if required not in compliance:
                    return False
            elif key == "failed_logins_max":
                if int(user_profile.get("failed_login_attempts", 0)) > value:
                    return False
            elif key == "sensitivity":
                if user_profile.get("access_sensitivity_level", "").lower() != str(value).lower():
                    return False
        
        return True


class GeneticAlgorithmOptimizer:
    """Genetic algorithm for policy optimization"""
    
    def __init__(
        self,
        population_size: int = 20,
        generations: int = 50,
        mutation_rate: float = 0.3,
        crossover_rate: float = 0.7,
        tournament_size: int = 3
    ):
        self.population_size = population_size
        self.generations = generations
        self.mutation_rate = mutation_rate
        self.crossover_rate = crossover_rate
        self.tournament_size = tournament_size
        self.population: List[AccessPolicy] = []
        self.fitness_history: List[float] = []
    
    def initialize_population(self) -> List[AccessPolicy]:
        """Create initial population of random policies"""
        self.population = []
        
        for _ in range(self.population_size):
            rules = self._generate_random_policy()
            policy = AccessPolicy(rules=rules, generation=0)
            self.population.append(policy)
        
        return self.population
    
    def _generate_random_policy(self) -> List[PolicyRule]:
        """Generate a random access control policy"""
        roles = ["admin", "manager", "developer", "analyst", "guest"]
        departments = ["engineering", "finance", "hr", "it", "security"]
        sensitivities = ["low", "medium", "high"]
        actions = ["approve", "deny", "review"]
        
        num_rules = random.randint(5, 12)
        rules = []
        
        for i in range(num_rules):
            condition = {}
            
            # Randomly select conditions
            if random.random() > 0.5:
                condition["role"] = random.choice(roles)
            
            if random.random() > 0.6:
                condition["risk_max"] = round(random.uniform(0.2, 0.8), 2)
            
            if random.random() > 0.7:
                condition["compliance"] = random.choice(["compliant", "non_compliant"])
            
            if random.random() > 0.7:
                condition["sensitivity"] = random.choice(sensitivities)
            
            if random.random() > 0.8:
                condition["failed_logins_max"] = random.randint(1, 5)
            
            if not condition:
                condition["risk_max"] = 1.0
            
            rule = PolicyRule(
                name=f"rule_{i}_{random.randint(1000, 9999)}",
                condition=condition,
                action=random.choice(actions),
                priority=random.randint(1, 100),
                weight=round(random.uniform(0.5, 2.0), 2)
            )
            rules.append(rule)
        
        return rules
    
    def evaluate_fitness(
        self,
        policy: AccessPolicy,
        test_dataset: List[Dict[str, Any]]
    ) -> float:
        """
        Calculate fitness score for a policy
        
        Fitness = (Security Score + Usability Score - Violations) * Compliance Factor
        """
        if not test_dataset:
            return random.uniform(0.5, 0.8)
        
        security_score = 0.0
        usability_score = 0.0
        violations = 0
        
        for profile in test_dataset:
            decision, _ = policy.evaluate_request(profile)
            
            # Security: penalize low-risk approvals and high-risk denials
            risk = float(profile.get("risk_score", 0.5))
            sensitivity = str(profile.get("access_sensitivity_level", "low")).lower()
            compliance = str(profile.get("compliance_status", "compliant")).lower()
            
            if decision == "approve":
                if risk > 0.66:
                    violations += 1
                elif "non" in compliance and sensitivity == "high":
                    violations += 1
                else:
                    security_score += 0.5
            
            elif decision == "deny":
                if risk < 0.33 and "compliant" in compliance:
                    violations += 1
                else:
                    security_score += 0.3
            
            else:  # review
                security_score += 0.4
            
            # Usability: reward approvals for compliant, low-risk users
            if decision == "approve" and "compliant" in compliance and risk < 0.4:
                usability_score += 1.0
            elif decision == "review" and risk >= 0.33 and risk <= 0.66:
                usability_score += 0.5
        
        # Normalize scores
        security_score = security_score / len(test_dataset)
        usability_score = usability_score / len(test_dataset)
        violations_penalty = (violations / len(test_dataset)) * 2.0
        
        # Compliance factor
        compliance_factor = 1.0 - (violations / len(test_dataset))
        
        # Calculate final fitness
        fitness = (security_score * 0.4 + usability_score * 0.4 - violations_penalty * 0.2) * compliance_factor
        
        # Bonus for smaller rule set (Occam's razor)
        rule_penalty = len(policy.rules) / 100.0
        fitness -= rule_penalty
        
        return max(0.0, fitness)
    
    def select_parent(self) -> AccessPolicy:
        """Tournament selection to pick a parent policy"""
        tournament = random.sample(self.population, self.tournament_size)
        return max(tournament, key=lambda p: p.fitness_score)
    
    def crossover(self, parent1: AccessPolicy, parent2: AccessPolicy) -> AccessPolicy:
        """Single-point crossover between two parent policies"""
        if random.random() > self.crossover_rate:
            return parent1.copy()
        
        crossover_point = random.randint(1, min(len(parent1.rules), len(parent2.rules)) - 1)
        
        child_rules = (
            parent1.rules[:crossover_point] +
            parent2.rules[crossover_point:]
        )
        
        child = AccessPolicy(rules=child_rules, generation=parent1.generation + 1)
        return child
    
    def mutate(self, policy: AccessPolicy) -> AccessPolicy:
        """Apply mutations to a policy"""
        if random.random() > self.mutation_rate:
            return policy.copy()
        
        mutated = policy.copy()
        mutation_type = random.choice(["modify_rule", "add_rule", "remove_rule", "reorder"])
        
        if mutation_type == "modify_rule" and mutated.rules:
            rule_idx = random.randint(0, len(mutated.rules) - 1)
            old_rule = mutated.rules[rule_idx]
            
            # Modify the rule
            new_condition = old_rule.condition.copy()
            param = random.choice(list(new_condition.keys()))
            
            if param == "risk_max":
                new_condition[param] = round(new_condition[param] + random.uniform(-0.1, 0.1), 2)
            elif param == "priority":
                new_condition[param] = old_rule.priority + random.randint(-20, 20)
            
            mutated.rules[rule_idx] = PolicyRule(
                name=old_rule.name,
                condition=new_condition,
                action=old_rule.action,
                priority=old_rule.priority + random.randint(-10, 10),
                weight=round(old_rule.weight + random.uniform(-0.2, 0.2), 2)
            )
        
        elif mutation_type == "add_rule":
            new_rule = self._generate_random_policy()[0]
            mutated.rules.append(new_rule)
        
        elif mutation_type == "remove_rule" and len(mutated.rules) > 3:
            mutated.rules.pop(random.randint(0, len(mutated.rules) - 1))
        
        elif mutation_type == "reorder":
            random.shuffle(mutated.rules)
        
        return mutated
    
    def evolve(self, test_dataset: List[Dict[str, Any]]) -> List[AccessPolicy]:
        """
        Run the genetic algorithm evolution
        
        Args:
            test_dataset: List of user profiles to test policies against
        
        Returns:
            Best policies from final generation
        """
        # Initialize population
        self.initialize_population()
        
        for generation in range(self.generations):
            # Evaluate fitness
            for policy in self.population:
                policy.fitness_score = self.evaluate_fitness(policy, test_dataset)
                policy.generation = generation
            
            # Track best fitness
            best_fitness = max(p.fitness_score for p in self.population)
            self.fitness_history.append(best_fitness)
            
            # Selection and reproduction
            new_population = []
            
            # Elitism: keep top 20% of population
            elite_size = max(1, self.population_size // 5)
            elite = sorted(self.population, key=lambda p: p.fitness_score, reverse=True)[:elite_size]
            new_population.extend(elite)
            
            # Generate new individuals
            while len(new_population) < self.population_size:
                parent1 = self.select_parent()
                parent2 = self.select_parent()
                
                child = self.crossover(parent1, parent2)
                child = self.mutate(child)
                
                new_population.append(child)
            
            self.population = new_population[:self.population_size]
        
        # Return best policies
        return sorted(self.population, key=lambda p: p.fitness_score, reverse=True)[:5]


def optimize_policies(
    training_data: List[Dict[str, Any]],
    population_size: int = 20,
    generations: int = 50
) -> Dict[str, Any]:
    """
    Main function: Optimize access control policies using genetic algorithm
    
    Args:
        training_data: Historical access logs for training
        population_size: Number of policies in population
        generations: Number of generations to evolve
    
    Returns:
        Optimization report with best policies
    """
    
    # Initialize GA
    ga = GeneticAlgorithmOptimizer(
        population_size=population_size,
        generations=generations,
        mutation_rate=0.3,
        crossover_rate=0.8
    )
    
    # Evolve policies
    best_policies = ga.evolve(training_data)
    
    # Format results
    return {
        "algorithm": "Genetic Algorithm",
        "generations_run": generations,
        "final_population_size": len(ga.population),
        "best_fitness_history": ga.fitness_history,
        "best_policies": [
            {
                "rank": i + 1,
                "fitness_score": round(p.fitness_score, 4),
                "num_rules": len(p.rules),
                "rules": [r.to_dict() for r in p.rules]
            }
            for i, p in enumerate(best_policies)
        ],
        "improvement": round(
            (ga.fitness_history[-1] - ga.fitness_history[0]) / max(abs(ga.fitness_history[0]), 0.001) * 100,
            2
        ) if ga.fitness_history else 0,
        "recommendation": f"Policy rank 1 achieved optimal fitness of {best_policies[0].fitness_score:.4f}"
    }
