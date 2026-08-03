import cv2


def draw_box(
    frame,
    x1,
    y1,
    x2,
    y2,
    color,
    label
):

    cv2.rectangle(
        frame,
        (x1, y1),
        (x2, y2),
        color,
        2
    )

    cv2.putText(
        frame,
        label,
        (x1, y1 - 10),
        cv2.FONT_HERSHEY_SIMPLEX,
        0.6,
        color,
        2
    )


def draw_counting_line(
    frame,
    line_y
):

    cv2.line(
        frame,
        (0, line_y),
        (frame.shape[1], line_y),
        (0, 255, 255),
        2
    )


def draw_counter(
    frame,
    total
):

    cv2.rectangle(
        frame,
        (0, 0),
        (220, 100),
        (0, 0, 0),
        -1
    )

    cv2.putText(
        frame,
        f"Masuk : {total['enter']}",
        (15, 30),
        cv2.FONT_HERSHEY_SIMPLEX,
        0.8,
        (255,255,255),
        2
    )

    cv2.putText(
        frame,
        f"Keluar : {total['exit']}",
        (15, 60),
        cv2.FONT_HERSHEY_SIMPLEX,
        0.8,
        (255,255,255),
        2
    )

    cv2.putText(
        frame,
        f"Total : {total['total']}",
        (15, 90),
        cv2.FONT_HERSHEY_SIMPLEX,
        0.8,
        (255,255,255),
        2
    )