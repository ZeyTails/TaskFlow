# TaskFlow MVP Plan (V1) — Version détaillée

## 1) Contexte et objectif

TaskFlow est une application web collaborative permettant à des utilisateurs authentifiés de travailler en équipe autour de **workspaces**, de **projets** et de **tâches**, avec :

* assignation de tâches,
* suivi d’avancement (statuts),
* priorités,
* échéances,
* commentaires,
* rôles et permissions.

**But pédagogique :** mettre en pratique la conception (modèle de données, UML, règles d’accès) et la réalisation (Laravel, migrations, policies, tests, seeders) d’une application multi-utilisateur.

---

## 2) Public cible & cas d’usage

### Public cible

* Groupes d’étudiants (projets académiques)
* Petites équipes (3 à 20 utilisateurs)

### Cas d’usage typiques

* Une équipe crée un workspace “Projet fin d’année”.
* L’owner invite les membres.
* Les membres créent un projet “Backend”, “Frontend”.
* Ils créent des tâches, s’assignent du travail, changent les statuts, commentent.
* Le viewer peut suivre l’avancement sans modifier.

---

## 3) Périmètre MVP (MoSCoW)

### MUST HAVE (obligatoire V1)

**Authentification**

* Inscription, connexion, déconnexion
* Accès protégé par middleware `auth`

**Workspaces (équipe)**

* Créer un workspace
* Lister ses workspaces
* Inviter/ajouter des membres (par email)
* Rôles : `owner`, `member`, `viewer`

**Projets**

* CRUD projet dans un workspace
* Accès uniquement aux membres du workspace

**Tâches**

* CRUD tâche dans un projet
* Champs : titre, description, statut, priorité, échéance
* Assignation à un utilisateur du workspace/projet
* Vue liste + filtres minimum (statut, assigné, priorité)

**Commentaires**

* Ajouter / lister des commentaires sur une tâche

**Sécurité/permissions**

* Policies Laravel appliquées (pas juste “caché dans l’UI”)

### SHOULD HAVE (si temps, recommandé)

* Page **Mes tâches** (toutes les tâches assignées à moi, cross-projets)
* Dashboard avec stats simples (tâches en retard, à faire, en cours)
* Historique d’activité minimal (ex : “status changed”)

### COULD HAVE (bonus)

* Vue Kanban dans un projet (todo / in_progress / done)
* Recherche globale (topbar)
* Calendrier simple (liste par date d’échéance)

### WON’T HAVE (hors V1)

* Chat temps réel
* Pièces jointes lourdes
* Gantt/Calendar avancé
* Intégrations (Slack, Google Calendar)
* Notifications email avancées

---

## 4) Règles métier (Business rules)

* Un utilisateur ne voit que les workspaces dont il est membre.
* Un projet appartient à un workspace.
* Une tâche appartient à un projet.
* L’assignation d’une tâche ne peut se faire qu’à un membre du workspace (ou projet).
* Statuts autorisés : `todo`, `in_progress`, `done` (enum ou constantes).
* Priorités : `low`, `medium`, `high`.
* Une échéance peut être vide (nullable), mais si renseignée elle doit être une date valide.
* Un `viewer` est strictement en lecture seule.

---

## 5) Modèle de données MVP (DB)

### Tables et champs (proposition)

* `users` : id, name, email (unique), password, timestamps
* `workspaces` : id, name, owner_id, timestamps
* `workspace_user` : workspace_id, user_id, role, timestamps
* `projects` : id, workspace_id, name, description (nullable), timestamps
* `tasks` :

  * id, project_id
  * title, description (nullable)
  * status (todo/in_progress/done)
  * priority (low/medium/high)
  * due_date (nullable)
  * assignee_id (nullable)
  * created_by (user_id)
  * timestamps
* `task_comments` : id, task_id, user_id, content, timestamps

### Contraintes de données recommandées

* `workspace_user` : contrainte unique sur `(workspace_id, user_id)`
* `workspaces.owner_id` : FK vers `users.id`
* `projects.workspace_id` : FK avec suppression en cascade des projets si workspace supprimé
* `tasks.project_id` : FK avec suppression en cascade des tâches si projet supprimé
* `tasks.assignee_id` : FK nullable vers `users.id` (set null à la suppression utilisateur)
* `tasks.created_by` : FK vers `users.id`
* `task_comments.task_id` : FK cascade
* `task_comments.user_id` : FK vers `users.id`

### Relations

* User ⟷ Workspace : many-to-many via `workspace_user` (avec role)
* Workspace → Projects : 1-to-many
* Project → Tasks : 1-to-many
* Task → Comments : 1-to-many
* Task → Assignee : belongsTo(User)

---

## 6) UX / UI (navigation définie)

### Layout global (après login)

**Sidebar (navigation principale)**

* Dashboard
* Mes tâches
* Workspaces / Projets (selon design)
* Calendrier (option)
* Paramètres (option)
* (bottom) Profil, Déconnexion

**Topbar (actions globales)**

* Recherche (placeholder ou MVP)
* Bouton “+” : nouvelle tâche / nouveau projet
* Menu utilisateur (profil / logout)

### Écrans MVP (détaillés)

1. Auth : Login / Register
2. Dashboard :

   * stats simples : total tâches assignées, en retard, à faire aujourd’hui
   * workspaces/projets récents
3. Mes tâches :

   * liste tâches assignées à moi
   * filtres : statut, priorité, due_date
4. Workspaces :

   * liste + créer workspace
   * détail workspace : membres + projets
5. Projets :

   * liste projets (dans workspace)
   * détail projet : liste tâches (table) + (bonus) colonnes/kanban
6. Détail tâche :

   * infos + changer statut/priorité/échéance + assigner
   * commentaires
7. Gestion membres (owner) :

   * ajouter par email
   * retirer
   * changer rôle (option)

---

## 7) Permissions (Policies Laravel)

### WorkspacePolicy

* `view` : membre du workspace
* `update/delete` : owner
* `manageMembers` : owner

### ProjectPolicy

* `view` : membre du workspace
* `create/update/delete` :

  * owner et member (pas viewer)

### TaskPolicy

* `view` : membre du workspace
* `create/update/delete` : owner/member
* `assign` : owner/member

### CommentPolicy

* `create` : owner/member
* `delete` : auteur OU owner (au choix)

### Stratégie d'erreur d'accès (à fixer dès le début)

* Option simple recommandée pour V1 : `403` si non autorisé
* Option renforcée : `404` pour masquer les ressources hors workspace
* Le choix doit être appliqué de manière cohérente dans toute l'app + tests

---

## 8) Routes Laravel (structure proposée)

*(indicatif, pour cadrer le dev)*

* `/login`, `/register`, `/logout`
* `/dashboard`
* `/workspaces`
* `/workspaces/{workspace}`
* `/workspaces/{workspace}/members`
* `/workspaces/{workspace}/projects`
* `/workspaces/{workspace}/projects/{project}`
* `/projects/{project}/tasks`
* `/tasks/{task}`
* `/tasks/{task}/comments`
* `/my-tasks`

---

## 9) Seeders & démo (important pour soutenance)

### Seeder de démonstration

* 3 users : Owner, Member, Viewer
* 1 workspace “Groupe Info”
* 2 projets : “Frontend”, “Backend”
* 8–12 tâches réparties (todo/in_progress/done)
* 1–2 tâches en retard (due_date passée)
* commentaires sur 2 tâches

### Scénario de démo (script soutenance)

1. Owner se connecte → crée workspace → invite member/viewer
2. Owner crée projet → crée tâches → assigne au member
3. Member se connecte → “Mes tâches” → change statut → ajoute commentaire
4. Viewer se connecte → voit tout mais ne peut pas modifier (preuve permissions)

---

## 10) Critères d’acceptation MVP

* Auth OK + routes protégées
* CRUD workspace/projet/tâche fonctionnels
* Assignation fonctionne et respecte l’appartenance au workspace
* Statuts/priorités validés côté serveur
* Commentaires fonctionnels
* Policies réellement appliquées (403 si non autorisé)
* Seeders disponibles + scénario démo exécutable
* Tests essentiels passent

---

## 10.1) Exigences non fonctionnelles minimales (projet étudiant)

* Temps de réponse acceptable en local (navigation fluide sur jeu de données seedé)
* Validation serveur sur tous les formulaires critiques
* Messages d'erreur clairs pour l'utilisateur
* Aucun secret exposé dans le dépôt (`.env` exclu)

---

## 11) Tests essentiels (Pest) — liste minimale

* Auth : register/login/logout
* Workspace : owner peut créer, member ne peut pas gérer membres, viewer lecture seule
* Accès : un non-membre reçoit 403/404 selon stratégie
* Task : create/update/status change, assignation
* Comment : create (owner/member), interdit viewer

---

## 12) Definition of Done (V1)

* Migrations + Models + Relations OK
* Controllers/Requests propres (validation)
* Policies actives
* UI fonctionnelle + navigation (sidebar/topbar)
* Seeders + README
* Tests critiques verts
