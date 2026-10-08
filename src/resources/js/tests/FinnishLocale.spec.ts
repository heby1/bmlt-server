import { afterEach, beforeAll, beforeEach, describe, expect, test, vi } from 'vitest';
import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/svelte';
import type { Meeting } from 'bmlt-server-client';
import MeetingEditForm from '../components/MeetingEditForm.svelte';
import MeetingsList from '../components/MeetingsList.svelte';
import { translations } from '../stores/localization';
import { meetingsState } from '../stores/meetingsState';
import { allFormats, allMeetings, allServiceBodies, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

beforeAll(sharedBeforeAll);
beforeEach(() => {
  sharedBeforeEach();
  translations.setLanguage('fi');
});
afterEach(async () => {
  cleanup();
  await sharedAfterEach();
  translations.setLanguage('en');
});

const meetings: Meeting[] = [
  { ...allMeetings[0], id: 5000, name: 'Sunday meeting', day: 0, startTime: '10:00' },
  { ...allMeetings[0], id: 5001, name: 'Monday evening', day: 1, startTime: '18:00' },
  { ...allMeetings[0], id: 5002, name: 'Tuesday meeting', day: 2, startTime: '10:00' },
  { ...allMeetings[0], id: 5003, name: 'Monday morning', day: 1, startTime: '09:00' }
];

describe('Finnish meeting presentation', () => {
  test('weekday selector starts on Monday while keeping Sunday value 0 and untranslated formats available', () => {
    render(MeetingEditForm, {
      props: {
        selectedMeeting: meetings[0],
        serviceBodies: allServiceBodies,
        formats: allFormats,
        onSaved: vi.fn(),
        onClosed: vi.fn(),
        onDeleted: vi.fn()
      }
    });

    const day = screen.getByRole('combobox', { name: 'Viikonpäivä' }) as HTMLSelectElement;
    const options = Array.from(day.options).filter((option) => option.value !== '');
    expect(options.map((option) => option.value)).toEqual(['1', '2', '3', '4', '5', '6', '0']);
    expect(options[0].textContent).toBe('Maanantai');
    expect(options[6].textContent).toBe('Sunnuntai');
    expect(day.value).toBe('0');

    const formats = document.querySelector('select[name="formatIds"]') as HTMLSelectElement;
    const basicText = Array.from(formats.options).find((option) => option.value === '19');
    expect(basicText?.textContent).toContain('Basic Text');
  });

  test('Finnish filters preserve weekday IDs and list sorting puts Sunday after Monday', async () => {
    meetingsState.update((state) => ({ ...state, meetings }));
    const { container } = render(MeetingsList, { props: { serviceBodies: allServiceBodies, formats: allFormats } });
    const rows = () => Array.from(container.querySelectorAll('tbody tr'));
    const names = () => rows().map((row) => row.querySelectorAll('td')[2].textContent?.trim());
    const days = () => rows().map((row) => row.querySelectorAll('td')[0].textContent?.trim());

    await waitFor(() => expect(names()).toEqual(['Monday morning', 'Monday evening', 'Tuesday meeting', 'Sunday meeting']));
    await fireEvent.mouseDown(screen.getByRole('button', { name: translations.getString('day') }));
    await waitFor(() => {
      const choices = Array.from(container.querySelectorAll<HTMLInputElement>('input[name="weekdays"]'));
      expect(choices.map((choice) => choice.value)).toEqual(['1', '2', '3', '4', '5', '6', '0']);
    });

    const dayHeading = screen.getByRole('columnheader', { name: translations.getString('day') });
    await fireEvent.click(dayHeading);
    expect(days()).toEqual(['Maanantai', 'Maanantai', 'Tiistai', 'Sunnuntai']);
    await fireEvent.click(dayHeading);
    expect(days()).toEqual(['Sunnuntai', 'Tiistai', 'Maanantai', 'Maanantai']);
  });

  test('English keeps Sunday-first presentation', async () => {
    translations.setLanguage('en');
    meetingsState.update((state) => ({ ...state, meetings }));
    const { container } = render(MeetingsList, { props: { serviceBodies: allServiceBodies, formats: allFormats } });
    await waitFor(() => {
      const rows = Array.from(container.querySelectorAll('tbody tr'));
      expect(rows.map((row) => row.querySelectorAll('td')[2].textContent?.trim())).toEqual(['Sunday meeting', 'Monday morning', 'Monday evening', 'Tuesday meeting']);
    });
  });
});
