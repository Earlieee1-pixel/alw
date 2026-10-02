import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * useAuth — makuha ang current user ug role helpers bisan asa nga component.
 * Gamiton: const { user, isAdmin, isLeader, isMember } = useAuth()
 */
export function useAuth() {
    const page = usePage()

    // Current logged-in user gikan sa shared Inertia props
    const user = computed(() => page.props.auth?.user ?? null)

    // Roles array sa user
    const roles = computed(() => user.value?.roles ?? [])

    // Role check helpers
    const isAdmin  = computed(() => roles.value.includes('admin'))
    const isLeader = computed(() => roles.value.includes('leader') || isAdmin.value)
    const isMember = computed(() => roles.value.includes('member') || isLeader.value)

    // Check kung ang user kay naa sa specific role
    function hasRole(role) {
        return roles.value.includes(role)
    }

    // Check kung ang user kay naa sa bisan usa sa mga roles
    function hasAnyRole(roleList) {
        return roleList.some(r => roles.value.includes(r))
    }

    return {
        user,
        roles,
        isAdmin,
        isLeader,
        isMember,
        hasRole,
        hasAnyRole,
    }
}
