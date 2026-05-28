export const MOBILE_BREAKPOINT = 768

export function isInitialMobileViewport() {
  if (typeof window === 'undefined') {
    return false
  }

  return window.innerWidth < MOBILE_BREAKPOINT
}
