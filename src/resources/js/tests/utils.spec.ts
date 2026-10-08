import { afterEach, describe, it, expect, vi } from 'vitest';
import { is24hrTime, stripLegacyFieldSeparator } from '../lib/utils';
import { translations } from '../stores/localization';

describe('Finnish clock format', () => {
  afterEach(() => {
    vi.restoreAllMocks();
    translations.setLanguage('en');
  });

  it('uses a 24-hour clock even when the browser uses AM/PM', () => {
    vi.spyOn(Date.prototype, 'toLocaleTimeString').mockReturnValue('6:30:00 PM');
    translations.setLanguage('fi');
    expect(is24hrTime()).toBe(true);
  });

  it('preserves browser clock preferences for other languages', () => {
    vi.spyOn(Date.prototype, 'toLocaleTimeString').mockReturnValue('6:30:00 PM');
    translations.setLanguage('en');
    expect(is24hrTime()).toBe(false);
  });
});

describe('stripLegacyFieldSeparator', () => {
  it('should strip legacy separator and return value', () => {
    expect(stripLegacyFieldSeparator('Bus Lines#@-@#16')).toBe('16');
    expect(stripLegacyFieldSeparator('Train Lines#@-@#Green Line D')).toBe('Green Line D');
    expect(stripLegacyFieldSeparator('Boat Line#@-@#Steamship Authority')).toBe('Steamship Authority');
  });

  it('should return unchanged value when no separator present', () => {
    expect(stripLegacyFieldSeparator('16')).toBe('16');
    expect(stripLegacyFieldSeparator('Green Line D')).toBe('Green Line D');
    expect(stripLegacyFieldSeparator('Regular value')).toBe('Regular value');
  });

  it('should handle null and undefined values', () => {
    expect(stripLegacyFieldSeparator(null)).toBe('');
    expect(stripLegacyFieldSeparator(undefined)).toBe('');
  });

  it('should handle empty string', () => {
    expect(stripLegacyFieldSeparator('')).toBe('');
  });

  it('should trim whitespace from extracted value', () => {
    expect(stripLegacyFieldSeparator('Bus Lines#@-@#  16  ')).toBe('16');
    expect(stripLegacyFieldSeparator('Train Lines#@-@#   Green Line D   ')).toBe('Green Line D');
  });

  it('should handle multiple separators by using only the first split', () => {
    // split with limit 2 means only first occurrence is split
    expect(stripLegacyFieldSeparator('First#@-@#Second#@-@#Third')).toBe('Second');
  });

  it('should return original value if nothing after separator', () => {
    expect(stripLegacyFieldSeparator('Bus Lines#@-@#')).toBe('Bus Lines#@-@#');
  });
});
